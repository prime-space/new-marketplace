<?php namespace App\Crud\Unit\CartOrderOnly;

use App\Cart\CartManager;
use App\Crud\Transformer\ConvertMoney;
use App\Entity\Cart;
use App\Entity\Customer;
use App\Payment\PaymentInitializer;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Token\Exception\TokenNotFoundException;
use LogicException;

class CartOrderOnlyCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'cartOrderOnly';

    private $cartManager;
    private $paymentInitializer;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CartManager $cartManager,
        PaymentInitializer $paymentInitializer
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->cartManager = $cartManager;
        $this->paymentInitializer = $paymentInitializer;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Cart::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $cart = $this->cartManager->findOrderOnlyCartByHeader();
        $cartId = null === $cart ? 0 : $cart->id;

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'id', $cartId),
        ];
    }

    public function getReadOneFields(): array
    {
        try {
            $token = $this->cartManager->getTokenByHeader();
        } catch (TokenNotFoundException $e) {
            throw new LogicException('Token is expected here', 0, $e);
        }

        return [
            'id',
            'statusId',
            'createdDate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE),
            'backToPaymentFormData' => function (Cart $cart) use ($token) {
                /** @var Customer $customer */
                $customer = $this->repositoryProvider->get(Customer::class)->findById($cart->customerId);

                return $this->paymentInitializer->init($customer, $cart, $token)->toArray();
            },
            'totalProductsAmountView' => [
                new ConvertMoney('totalProductsAmount', true),
            ],
        ];
    }

    public function getReadListFields(): array
    {
        return [];
    }
}
