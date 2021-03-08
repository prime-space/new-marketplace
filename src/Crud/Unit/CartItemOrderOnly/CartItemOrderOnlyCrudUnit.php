<?php namespace App\Crud\Unit\CartItemOrderOnly;

use App\Cart\CartManager;
use App\Crud\Transformer\ConvertMoney;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\ProductObject;
use App\Entity\Review;
use App\Entity\StorageFile;
use App\Entity\User;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;

class CartItemOrderOnlyCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'cartItemOrderOnly';

    private $cartManager;
    private $cdn;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CartManager $cartManager,
        string $cdn
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->cartManager = $cartManager;
        $this->cdn = $cdn;
    }

    public function getUnitName(): string
    {
        return static::NAME;
    }

    public function getEntityClass(): string
    {
        return CartItem::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $cart = $this->cartManager->findOrderOnlyCartByHeader();
        if (null === $cart || Cart::STATUS_ID_PAID !== $cart->statusId) {
            $cartId = 0;
        } else {
            $cartId = $cart->id;
        }

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'cartId', $cartId),
        ];
    }

    public function getReadOneFields(): array
    {
        $fields = $this->getReadListFields();
        $fields['objects'] = function (CartItem $cartItem) {
            /** @var ProductObject[] $objects */
            $objects = $this->repositoryProvider->get(ProductObject::class)
                ->findBy(['id' => $cartItem->productObjectIds]);

            return array_column($objects, 'data');
        };

        return array_merge($this->getReadListFields(), [
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
            'review' => function (CartItem $cartItem) {
                /** @var Review|null $review */
                $review = $this->repositoryProvider->get(Review::class)->findOneBy([
                    Review::FIELD_CART_ITEM_ID => $cartItem->id,
                    Review::FIELD_IS_DELETED => 0,
                ]);

                if (null !== $review) {
                    return new ReadTransformer\TransformersGroup($review, Review::getViewTransformerFields());
                } else {
                    return null;
                }
            },
        ]);
    }

    public function getReadListFields(): array
    {
        return [
            CartItem::FIELD_ID,
            'name' => new ReadTransformer\Entity('productId', Product::class, 'name'),
            'image' => [
                new ReadTransformer\Entity('productId', Product::class),
                new ReadTransformer\Entity('imageStorageFileId', StorageFile::class, ['compilePath', [$this->cdn]]),
            ],
            'seller' => [
                new ReadTransformer\Entity('productId', Product::class),
                new ReadTransformer\Entity('userId', User::class, 'getName'),
            ],
            'priceView' => [
                new ConvertMoney('price', true),
            ],
            'amount',
            'amountInFact',
            'isCustomerNotificationsDisabled',
        ];
    }
}
