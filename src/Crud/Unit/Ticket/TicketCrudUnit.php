<?php namespace App\Crud\Unit\Ticket;

use App\Api\Item\Admin\Handler\TicketApiHandler;
use App\Entity\Ticket;
use App\MessageBroker\MessageBrokerConfig;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit as UnitMethod;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class TicketCrudUnit extends UnitAbstract implements
    UnitMethod\ReadMethodInterface,
    UnitMethod\CreateMethodInterface
{
    const NAME = 'ticket';

    const FORM_FIELD_SUBJECT = 'subject';
    const FORM_FIELD_MESSAGE = 'message';

    private $translator;
    private $messageBroker;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        TranslatorInterface $translator,
        MessageBroker $messageBroker
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->translator = $translator;
        $this->messageBroker = $messageBroker;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Ticket::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'userId', $user->id),
        ];
    }

    public function getReadOneFields(): array
    {
        return [
            'messagesSendingNum',
            'messagesNum',
        ];
    }

    public function getReadListFields(): array
    {

        return [
            'id',
            'subject',
            'hasUnreadMessage',
            'lastMessageData' => new ReadTransformer\Date(
                Ticket::FIELD_LAST_MESSAGE_TS,
                ReadTransformer\Date::FORMAT_DATE
            ),
        ];
    }

    public function getCreateFormConfig(): FormConfig
    {

        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [],
        ]);
        $config
            ->addField(self::FORM_FIELD_SUBJECT, FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5, 'max' => 256]),
                ],
                'label' => 'Тема',
            ])
            ->addField(self::FORM_FIELD_MESSAGE, FormType\TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5, 'max' => 5000]),
                ],
                'label' => 'Сообщение',
                'mapped' => false,
            ]);

        return $config;
    }

    /** @param $entity Ticket */
    public function getMutationsOnCreate(object $entity): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }
        $mutations = [
            'userId' => $user->id,
        ];

        return $mutations;
    }

    /** @param $entity Ticket */
    public function onCreate(object $entity, array $formData): void
    {
        $data = [
            'serviveClass' => TicketApiHandler::class,
            'method' => 'create',
            'args' => [$entity->id, $formData[self::FORM_FIELD_MESSAGE]]
        ];
        $this->messageBroker->createMessage(MessageBrokerConfig::QUEUE_NAME_ADMIN_API_REQUEST, $data, 3);
    }
}
