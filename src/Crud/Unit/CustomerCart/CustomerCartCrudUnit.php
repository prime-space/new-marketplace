<?php namespace App\Crud\Unit\CustomerCart;

use App\Cart\CartManager;
use App\Controller\CustomerController;
use App\Crud\Transformer\ConvertMoney;
use App\Crud\Unit\CartOrderOnly\CartOrderOnlyCrudUnit;
use App\Customer\Token\CustomerToken;
use App\Entity\Cart;
use App\Payment\PaymentInitializer;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Token\Exception\TokenNotFoundException;
use Ewll\UserBundle\Token\TokenProvider;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerCartCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'customerCart';

    private $cartManager;
    private $requestStack;
    private $tokenProvider;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CartManager $cartManager,
        RequestStack $requestStack,
        TokenProvider $tokenProvider
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->cartManager = $cartManager;
        $this->requestStack = $requestStack;
        $this->tokenProvider = $tokenProvider;
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
        $tokenKey = $this->requestStack->getCurrentRequest()->cookies->get(CustomerController::COOKIE_NAME, '');
        try {
            $token = $this->tokenProvider->getByCode($tokenKey, CustomerToken::TYPE_ID);
            $customerId = $token->data[CustomerToken::DATA_KEY];
            $condition = new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'customerId', $customerId);
        } catch (TokenNotFoundException $e) {
            $condition = new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'id', 0);
        }

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'statusId', Cart::STATUS_ID_PAID),
            $condition,
        ];
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'createdDate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE),
            'totalProductsAmountView' => [
                new ConvertMoney('totalProductsAmount', true),
            ],
        ];
    }

    public function getReadOneFields(): array
    {
        return [
            'id',
            'statusId',
            'createdDate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE),
            'totalProductsAmountView' => [
                new ConvertMoney('totalProductsAmount', true),
            ],
        ];
    }
}
