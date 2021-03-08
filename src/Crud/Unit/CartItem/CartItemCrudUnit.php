<?php namespace App\Crud\Unit\CartItem;

use App\Cart\CartManager;
use App\Crud\Transformer\ConvertMoney;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\StorageFile;
use App\Entity\User;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\HttpFoundation\RequestStack;

class CartItemCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'cartItem';

    private $cartManager;
    private $requestStack;
    private $cdn;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CartManager $cartManager,
        RequestStack $requestStack,
        string $cdn
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->cartManager = $cartManager;
        $this->requestStack = $requestStack;
        $this->cdn = $cdn;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return CartItem::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $cart = $this->cartManager->getCart($this->requestStack->getCurrentRequest()->getClientIp());

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'cartId', $cart->id),
        ];
    }

    public function getReadOneFields(): array
    {
        return [
            'productId',
            'amount',
            'name' => new ReadTransformer\Entity('productId', Product::class, 'name'),
            'image' => [
                new ReadTransformer\Entity('productId', Product::class),
                new ReadTransformer\Entity('imageStorageFileId', StorageFile::class, ['compilePath', [$this->cdn]]),
            ],
            'seller' => [
                new ReadTransformer\Entity('productId', Product::class),
                new ReadTransformer\Entity('userId', User::class, 'getName'),
            ],
            'price' => [
                new ReadTransformer\Entity('productId', Product::class),
                new ConvertMoney('price'),
            ],
            'priceView' => [
                new ReadTransformer\Entity('productId', Product::class),
                new ConvertMoney('price', true),
            ],
            'soldsCount' => new ReadTransformer\Entity('productId', Product::class, 'salesNum'),
        ];
    }

    public function getReadListFields(): array
    {
        return $this->getReadOneFields();
    }
}
