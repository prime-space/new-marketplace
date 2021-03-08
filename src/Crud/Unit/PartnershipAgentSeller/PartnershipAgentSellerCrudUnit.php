<?php namespace App\Crud\Unit\PartnershipAgentSeller;

use App\Crud\Unit\PartnershipAgentSeller\CustomAction\Terminate;
use App\Entity\Partnership;
use App\Entity\User;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Date;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Entity;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;

class PartnershipAgentSellerCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'partnershipAgentSeller';

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator
    ) {
        parent::__construct($repositoryProvider, $authenticator);
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
        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'agentUserId', $user->id),
            new ExpressionCondition(
                ExpressionCondition::ACTION_EQUAL,
                'statusId',
                Partnership::STATUS_ID_OK
            )
        ];
    }

    public function getReadOneFields(): array
    {
        return $this->getReadListFields();
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'sellerName' => new Entity('sellerUserId', User::class, 'getName'),
            'contact' => new Entity('sellerUserId', User::class, 'contact'),
            'contactTypeId' => new Entity('sellerUserId', User::class, 'contactTypeId'),
            'fee',
            'dateCreate' => new Date('createdTs', Date::FORMAT_DATE),
        ];
    }

    public function getCustomActions(): array
    {
        return [Terminate::class];
    }
}
