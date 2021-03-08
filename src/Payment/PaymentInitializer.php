<?php namespace App\Payment;

use App\Cart\CartManager;
use App\Entity\Cart;
use App\Entity\Customer;
use Ewll\UserBundle\Entity\Token;
use Ewll\UserBundle\Token\TokenProvider;

class PaymentInitializer
{
    private $primepayerPaymentSystem;
    private $tokenProvider;
    private $cartManager;

    public function __construct(
        PrimepayerPaymentSystem $primepayerPaymentSystem,
        TokenProvider $tokenProvider,
        CartManager $cartManager
    ) {
        $this->primepayerPaymentSystem = $primepayerPaymentSystem;
        $this->tokenProvider = $tokenProvider;
        $this->cartManager = $cartManager;
    }

    public function init(Customer $customer, Cart $cart, Token $token): FormData
    {
        $successUrl = $this->cartManager->compileOrderOnlyUrl($cart, $token);
        $formData = $this->primepayerPaymentSystem->init(
            $cart->id,
            $cart->totalProductsAmount,
            "Оплата заказа #{$cart->id}",
            PrimepayerPaymentSystem::CURRENCY_RUB,
            $customer->email,
            $successUrl
        );

        return $formData;
    }
}
