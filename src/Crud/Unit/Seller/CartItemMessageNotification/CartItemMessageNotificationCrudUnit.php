<?php namespace App\Crud\Unit\Seller\CartItemMessageNotification;

use App\Entity\CartItem;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class CartItemMessageNotificationCrudUnit extends UnitAbstract implements
    UpdateMethodInterface
{
    const NAME = 'sellerCartItemMessageNotification';

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
        return CartItem::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    public function getAccessConditions(string $action): array
    {
        $user = $this->getUser();

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'productUserId', $user->id),
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'isPaid', true),
        ];
    }

    public function getUpdateFormConfig(object $entity): FormConfig
    {
        $config = new FormConfig(['data_class' => $this->getEntityClass()]);
        $config
            ->addField('isSellerNotificationsDisabled', FormType\CheckboxType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                ],
                'false_values' => ['0'],
            ]);

        return $config;
    }
}
