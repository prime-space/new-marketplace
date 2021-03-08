<?php namespace App\Customer;

use App\Cart\CartManager;
use App\Controller\CustomerController;
use App\Customer\Token\CustomerToken;
use App\Entity\Cart;
use Ewll\UserBundle\Token\Exception\TokenNotFoundException;
use Ewll\UserBundle\Token\TokenProvider;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerIdByRequestFinder
{
    private $cartManager;
    private $requestStack;
    private $tokenProvider;

    public function __construct(
        CartManager $cartManager,
        RequestStack $requestStack,
        TokenProvider $tokenProvider
    ) {
        $this->cartManager = $cartManager;
        $this->requestStack = $requestStack;
        $this->tokenProvider = $tokenProvider;
    }

    public function find(): int
    {
        if ($this->requestStack->getCurrentRequest()->headers->has(CartManager::TOKEN_HEADER_NAME)) {
            $cart = $this->cartManager->findOrderOnlyCartByHeader();
            if (null === $cart || Cart::STATUS_ID_PAID !== $cart->statusId) {
                $customerId = 0;
            } else {
                $customerId = $cart->customerId;
            }
        } else {
            try {
                $tokenKey = $this->requestStack->getCurrentRequest()->cookies->get(CustomerController::COOKIE_NAME, '');
                $token = $this->tokenProvider->getByCode($tokenKey, CustomerToken::TYPE_ID);
                $customerId = $token->data[CustomerToken::DATA_KEY];
            } catch (TokenNotFoundException $e) {
                $customerId = 0;
            }
        }

        return $customerId;
    }
}
