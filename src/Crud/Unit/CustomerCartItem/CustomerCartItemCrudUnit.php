<?php namespace App\Crud\Unit\CustomerCartItem;

use App\Cart\CartManager;
use App\Controller\CustomerController;
use App\Crud\Unit\CartItemOrderOnly\CartItemOrderOnlyCrudUnit;
use App\Customer\Token\CustomerToken;
use App\Entity\Cart;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Token\Exception\TokenNotFoundException;
use Ewll\UserBundle\Token\TokenProvider;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerCartItemCrudUnit extends CartItemOrderOnlyCrudUnit
{
    const NAME = 'customerCartItem';

    private $cartManager;
    private $cdn;
    private $requestStack;
    private $tokenProvider;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CartManager $cartManager,
        string $cdn,
        RequestStack $requestStack,
        TokenProvider $tokenProvider
    ) {
        parent::__construct($repositoryProvider, $authenticator, $cartManager, $cdn);
        $this->cartManager = $cartManager;
        $this->cdn = $cdn;
        $this->requestStack = $requestStack;
        $this->tokenProvider = $tokenProvider;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $tokenKey = $this->requestStack->getCurrentRequest()->cookies->get(CustomerController::COOKIE_NAME, '');
        try {
            $token = $this->tokenProvider->getByCode($tokenKey, CustomerToken::TYPE_ID);
            $customerId = $token->data[CustomerToken::DATA_KEY];
            //@TODO could be overhead
            $customerCartsIndexedById = $this->repositoryProvider->get(Cart::class)
                ->findBy(['customerId' => $customerId, 'statusId' => Cart::STATUS_ID_PAID], 'id');
            $customerCartIds = array_keys($customerCartsIndexedById);
            $condition = new ExpressionCondition(
                ExpressionCondition::ACTION_EQUAL,
                'cartId',
                $customerCartIds
            );
        } catch (TokenNotFoundException $e) {
            $condition = new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'id', 0);
        }

        return [$condition];
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig();
        $config
            ->addField('cartId', IntegerType::class);

        return $config;
    }
}
