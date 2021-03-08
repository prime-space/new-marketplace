<?php namespace App\Crud\Unit\PartnershipSellerSearchAgent;

use App\Crud\Unit\PartnershipSellerSearchAgent\CustomAction\Offer;
use App\Entity\Partnership;
use App\Entity\User;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Date;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;

class PartnershipSellerSearchAgentCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'partnershipSellerSearchAgent';

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
        return User::class;
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
            new ExpressionCondition(ExpressionCondition::ACTION_NOT_EQUAL, 'id', $user->id),
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'isPublicAgent', true),
            new RelationCondition(
                RelationCondition::COND_NOT_RELATE,
                Partnership::class,
                [
                    'field' => 'agentUserId',
                    'type' => 'field',
                    'action' => RelationCondition::ACTION_EQUAL,
                    'value' => 'id'
                ],
                [
                    [
                        'field' => 'sellerUserId',
                        'type' => 'value',
                        'action' => RelationCondition::ACTION_EQUAL,
                        'value' => $user->id
                    ],
                    [
                        'field' => 'statusId',
                        'type' => 'value',
                        'action' => RelationCondition::ACTION_NOT_EQUAL,
                        'value' => Partnership::STATUS_ID_REJECTED
                    ],
                ]
            ),
        ];
    }

    public function getReadOneFields(): array
    {
        return array_merge($this->getReadListFields(), ['agentInfo']);
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'name' => function (User $user) {
                return $user->getName();
            },
            'agentRating',
            'agentPartnershipsNum',
            'agentSalesNum',
            'createdDate' => new Date('createdTs', Date::FORMAT_DATE),
        ];
    }


    public function getCustomActions(): array
    {
        return [Offer::class];
    }
}
