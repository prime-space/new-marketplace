<?php namespace App\Crud\Unit\TicketMessage;

use App\Api\Item\Admin\Handler\TicketApiHandler;
use App\Crud\Source\AdminApiSource;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Unit as UnitMethod;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Form\ChoiceList\Loader\EntityChoiceLoader;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class TicketMessageCrudUnit extends UnitAbstract implements
    UnitMethod\ReadMethodInterface,
    UnitMethod\CreateMethodInterface
{
    const NAME = 'ticketMessage';

    const FORM_FIELD_TICKET_ID = 'ticketId';
    const FORM_FIELD_TEXT = 'message';

    private $translator;
    private $ticketApiHandler;
    private $messageBroker;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        TranslatorInterface $translator,
        TicketApiHandler $ticketApiHandler,
        MessageBroker $messageBroker
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->translator = $translator;
        $this->ticketApiHandler = $ticketApiHandler;
        $this->messageBroker = $messageBroker;
    }

    public function getSourceClassName(): string
    {
        return AdminApiSource::class;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return TicketMessageDto::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        //Доступ в фильтрах листинга и при создании чекается через choice_loader
        //@TODO UPD 2020.05.08 При запросе getReadOneFields фильтры не работают и это является уязвимостью
        //в DbSource это решается RelationCondition, но AdminApiSource их не понимает
        return [];
    }

    public function getReadListFields(): array
    {

        return [
            'text',
            'answerUserName',
            'createdTs',
        ];
    }

    public function getCreateFormConfig(): FormConfig
    {
        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [],
        ]);
        $config
            ->addField(self::FORM_FIELD_TICKET_ID, FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'choice_loader' => $this->getTicketIdChoiceLoader(),
            ])
            ->addField(self::FORM_FIELD_TEXT, FormType\TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5, 'max' => 5000]),
                ],
            ]);

        return $config;
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig();
        $config
            ->addField('ticketId', FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'choice_loader' => $this->getTicketIdChoiceLoader(),
            ]);

        return $config;
    }

    public function getReadListExtraData(array $context): array
    {
        $extraData = [];
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }

        $ticketId = $this->findTicketIdFromConditions($context['conditions']);
        if (null !== $ticketId) {
            /** @var TicketRepository $ticketRepository */
            $ticketRepository = $this->repositoryProvider->get(Ticket::class);
            /** @var Ticket|null $ticket */
            $ticket = $ticketRepository
                ->findOneBy([Ticket::FIELD_ID => $ticketId, Ticket::FIELD_USER_ID => $user->id]);
            if (null !== $ticket) {
                if ($ticket->hasUnreadMessage) {
                    $ticket->hasUnreadMessage = false;
                    $ticketRepository->update($ticket, ['hasUnreadMessage']);
                }
                $extraData['messagesSendingNum'] = $ticket->messagesSendingNum;
            }
        }

        return $extraData;
    }

    private function findTicketIdFromConditions(array $conditions): ?int
    {
        foreach ($conditions as $condition) {
            if ($condition instanceof ExpressionCondition && $condition->getField() === 'ticketId') {
                return $condition->getValue();
            }
        }

        return null;
    }

    private function getTicketIdChoiceLoader(): EntityChoiceLoader
    {
        $ticketIdChoiceLoader = new EntityChoiceLoader(
            $this->repositoryProvider->get(Ticket::class),
            function (Ticket $ticket) {
                return $ticket->id;
            },
            [new FilterExpression(FilterExpression::ACTION_EQUAL, 'userId', $this->getUser()->id)],
        );

        return $ticketIdChoiceLoader;
    }
}
