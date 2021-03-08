<?php namespace App\Crud\Unit\Customer\CartItemMessageNotification;

use App\Customer\CustomerIdByRequestFinder;
use App\Entity\CartItem;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class CartItemMessageNotificationCrudUnit extends UnitAbstract implements
    UpdateMethodInterface
{
    const NAME = 'customerCartItemMessageNotification';

    private $customerIdByRequestFinder;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CustomerIdByRequestFinder $customerIdByRequestFinder
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->customerIdByRequestFinder = $customerIdByRequestFinder;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return CartItem::class;
    }

    public function getAccessConditions(string $action): array
    {
        $customerId = $this->customerIdByRequestFinder->find();

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'customerId', $customerId),
        ];
    }

    public function getUpdateFormConfig(object $entity): FormConfig
    {
        $config = new FormConfig(['data_class' => $this->getEntityClass()]);
        $config
            ->addField('isCustomerNotificationsDisabled', FormType\CheckboxType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                ],
                'false_values' => ['0'],
            ]);

        return $config;
    }
}
