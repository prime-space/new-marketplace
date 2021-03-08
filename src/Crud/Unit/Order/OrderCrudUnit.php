<?php namespace App\Crud\Unit\Order;

use App\Crud\Filter;
use App\Crud\Transformer\MaskEmail;
use App\Entity\CustomerBlockedEntity;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Customer;
use App\Entity\Ip;
use App\Entity\Product;
use App\Entity\ProductObject;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\DeleteMethodInterface;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type as FormType;

class OrderCrudUnit extends UnitAbstract implements
    ReadMethodInterface,
    DeleteMethodInterface
{
    const NAME = 'order';

    private $translator;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        TranslatorInterface $translator
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->translator = $translator;
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

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $user = $this->getUser();

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'productUserId', $user->id),
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'isPaid', true),
        ];
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig([
            'data_class' => Filter::class,
        ]);
        $config
            ->addField(
                'id',
                FormType\IntegerType::class,
                ['label' => 'Номер заказа',],
            )
            ->addField(
                'cartId',
                FormType\IntegerType::class,
                ['label' => 'Номер счета',],
            )
            ->addField(
                'email',
                FormType\TextType::class,
                [
                    'label' => 'Email',
//                    'property_path' => Customer::FIELD_EMAIL,
                    'property_path' => sprintf('%s[%s]', CartItem::FIELD_CUSTOMER, Customer::FIELD_EMAIL),
                ],
            );

        return $config;
    }

    public function getReadListFields(): array
    {
        $currencyPlaceholder = sprintf('currency.%s.sign', ReadTransformer\Translate::PLACEHOLDER);

        return [
            'id',
            'cartId',
            'productName' => new ReadTransformer\Entity('productId', Product::class, 'name'),
            'price' => new ReadTransformer\Money('price'),
            'currency' => new ReadTransformer\Translate('currencyId', 'payment', $currencyPlaceholder),
            'dateCreate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE_TIME),
        ];
    }

    public function getReadOneFields(): array
    {
        $currencyPlaceholder = sprintf('currency.%s.sign', ReadTransformer\Translate::PLACEHOLDER);

        return [
            'id',
            'cartId',
            'productId',
            'amountInFact',
            'productName' => new ReadTransformer\Entity('productId', Product::class, 'name'),
            'price' => new ReadTransformer\Money('price'),
            'productPrice' => new ReadTransformer\Money('price'),
            'currency' => new ReadTransformer\Translate('currencyId', 'payment', $currencyPlaceholder),
            'customerId',
            'maskedCustomerIp' => [
                'value' => function(CartItem $cartItem) {
                    /** @var Cart $cart */
                    $cart = $this->repositoryProvider->get(Cart::class)->findById($cartItem->cartId);
                    if (null === $cart->customerIpId) {
                        return null;
                    }
                    /** @var Ip $ip */
                    $ip = $this->repositoryProvider->get(Ip::class)->findById($cart->customerIpId);
                    $pattern = '/(?!\d{1,3}\.\d{1,3}\.)\d/';
                    $replacement = '*';
                    $maskedIp = preg_replace($pattern, $replacement, $ip->ip);

                    return $maskedIp;
                },
            ],
            'customerBlockedByEmailId' => [
                function(CartItem $cartItem) {
                    /** @var Cart $cart */
                    $cart = $this->repositoryProvider->get(Cart::class)->findById($cartItem->cartId);
                    /** @var CustomerBlockedEntity | null $customerBlockedEntity */
                    $customerBlockedEntity = $this->repositoryProvider->get(CustomerBlockedEntity::class)->findOneBy([
                        CustomerBlockedEntity::FIELD_BLOCKED_BY_USER_ID => $this->getUser()->id,
                        CustomerBlockedEntity::FIELD_ENTITY_ID => $cart->customerId,
                        CustomerBlockedEntity::FIELD_ENTITY_TYPE => CustomerBlockedEntity::TYPE_ID_EMAIL,
                        CustomerBlockedEntity::FIELD_IS_DELETED => false,
                    ]);
                    $customerBlockedEntityId = null === $customerBlockedEntity ? null : $customerBlockedEntity->id;

                    return $customerBlockedEntityId;
                }
            ],
            'customerBlockedByIpId' => [
                function(CartItem $cartItem) {
                    /** @var Cart $cart */
                    $cart = $this->repositoryProvider->get(Cart::class)->findById($cartItem->cartId);
                    /** @var CustomerBlockedEntity | null $customerBlockedEntity */
                    $customerBlockedEntity = $this->repositoryProvider->get(CustomerBlockedEntity::class)->findOneBy([
                        CustomerBlockedEntity::FIELD_BLOCKED_BY_USER_ID => $this->getUser()->id,
                        CustomerBlockedEntity::FIELD_ENTITY_ID => $cart->customerIpId,
                        CustomerBlockedEntity::FIELD_ENTITY_TYPE => CustomerBlockedEntity::TYPE_ID_IP,
                        CustomerBlockedEntity::FIELD_IS_DELETED => false,
                    ]);
                    $customerBlockedEntityId = null === $customerBlockedEntity ? null : $customerBlockedEntity->id;

                    return $customerBlockedEntityId;
                }
            ],
            'customerIpId' => function(CartItem $cartItem) {
                /** @var Cart $cart */
                $cart = $this->repositoryProvider->get(Cart::class)->findById($cartItem->cartId);
                if (null === $cart->customerIpId) {
                    return null;
                }

                return $cart->customerIpId;
            },
            'dateCreate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE_TIME),
            'maskedCustomerEmail' => [
                new ReadTransformer\Entity(CartItem::FIELD_CART_ID, Cart::class),
                new ReadTransformer\Entity(Cart::FIELD_CUSTOMER_ID, Customer::class),
                new MaskEmail(Customer::FIELD_EMAIL),
            ],
            'objects' => function (CartItem $cartItem) {
                if (count($cartItem->productObjectIds) > 0) {
                    /** @var ProductObject[] $objects */
                    $objects = $this->repositoryProvider->get(ProductObject::class)
                        ->findBy(['id' => $cartItem->productObjectIds]);
                    $objectsData = array_column($objects, 'data');
                } else {
                    $objectsData = [];
                }

                return $objectsData;
            },
            'calculations',
        ];
    }
}
