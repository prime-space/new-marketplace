<?php namespace App\Crud\Unit\PartnershipSellerAgent;

use App\Crud\Unit\PartnershipSellerAgent\CustomAction\Terminate;
use App\Entity\Partnership;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Form\Constraint\Accuracy;
use App\Form\Constraint\NicknameIsSet;
use Ewll\CrudBundle\Action\ActionInterface;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Date;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Entity;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class PartnershipSellerAgentCrudUnit extends UnitAbstract implements
    ReadMethodInterface,
    UpdateMethodInterface
{
    const NAME = 'partnershipSellerAgent';
    private $eventFactory;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        EventFactory $eventFactory
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->eventFactory = $eventFactory;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Partnership::class;
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
        $accessConditions = [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'sellerUserId', $user->id),
        ];
        if (ActionInterface::CREATE !== $action) {
            $accessConditions[] = new ExpressionCondition(
                ExpressionCondition::ACTION_EQUAL,
                'statusId',
                Partnership::STATUS_ID_OK
            );
        }

        return $accessConditions;
    }

    public function getReadOneFields(): array
    {
        return $this->getReadListFields();
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'agentName' => new Entity('agentUserId', User::class, 'getName'),
            'contact' => new Entity('agentUserId', User::class, 'contact'),
            'contactTypeId' => new Entity('agentUserId', User::class, 'contactTypeId'),
            'fee',
            'dateCreate' => new Date('createdTs', Date::FORMAT_DATE),
        ];
    }

    private function createFormConfig(): FormConfig
    {
        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [new NicknameIsSet($this->getUser())],
        ]);
        $config
            ->addField('fee', FormType\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0, 'max' => 90]),
                    new Accuracy(2),
                ],
            ]);

        return $config;
    }

    /** @param $entity Partnership */
    public function getUpdateFormConfig(object $entity): FormConfig
    {
        return $this->createFormConfig();
    }

    public function getCustomActions(): array
    {
        return [Terminate::class];
    }
}
