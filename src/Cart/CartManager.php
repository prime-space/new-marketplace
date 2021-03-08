<?php namespace App\Cart;

use App\Currency\CurrencyConverter;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Currency;
use App\Entity\Customer;
use App\Entity\Ip;
use App\Entity\Product;
use App\Partner\PartnerManager;
use App\Repository\CartItemRepository;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Entity\Token;
use Ewll\UserBundle\Token\Exception\ActiveTokenExistsException;
use Ewll\UserBundle\Token\Exception\TokenNotFoundException;
use Ewll\UserBundle\Token\TokenProvider;
use Exception;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;

class CartManager
{
    const COOKIE_NAME = 'cart';
    const TOKEN_HEADER_NAME = 'cart-token';

    private $tokenProvider;
    private $requestStack;
    private $repositoryProvider;
    private $defaultDbClient;
    private $domain;
    private $currencyConverter;
    private $domainCustomer;
    private $partnerManager;

    public function __construct(
        TokenProvider $tokenProvider,
        RequestStack $requestStack,
        RepositoryProvider $repositoryProvider,
        DbClient $defaultDbClient,
        string $domain,
        CurrencyConverter $currencyConverter,
        string $domainCustomer,
        PartnerManager $partnerManager
    ) {
        $this->tokenProvider = $tokenProvider;
        $this->requestStack = $requestStack;
        $this->repositoryProvider = $repositoryProvider;
        $this->defaultDbClient = $defaultDbClient;
        $this->domain = $domain;
        $this->currencyConverter = $currencyConverter;
        $this->domainCustomer = $domainCustomer;
        $this->partnerManager = $partnerManager;
    }

    public function getCart(string $ip): Cart
    {
        $cart = $this->findNotFixedCart();
        if (null === $cart) {
            $cart = $this->initCart($ip);
        }

        return $cart;
    }

    public function getItemsAmount(): int
    {
        $amount = 0;
        $cart = $this->findNotFixedCart();
        if (null !== $cart) {
            /** @var CartItem[] $cartItems */
            $cartItems = $this->repositoryProvider->get(CartItem::class)
                ->findBy([CartItem::FIELD_CART_ID => $cart->id]);
            foreach ($cartItems as $cartItem) {
                $amount += $cartItem->amount;
            }
        }

        return $amount;
    }

    public function add(Cart $cart, int $productId): void
    {
        /** @var Product $product */
        $product = $this->repositoryProvider->get(Product::class)->findById($productId);
        $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
        /** @var CartItem|null $cartItem */
        $cartItem = $cartItemRepository->findOneBy(['cartId' => $cart->id, 'productId' => $product->id]);
        if (null !== $cartItem) {
            if ($cartItem->amount < 100) {
                $cartItem->amount++;
                $cartItemRepository->update($cartItem, ['amount']);
            }
        } else {
            $cartItem = CartItem::create($cart->id, $product->id, $product->userId, 1);
            $cartItemRepository->create($cartItem);
        }
    }

    public function setAmount(Cart $cart, int $productId, int $amount)
    {
        /** @var Product|null $product */
        $product = $this->repositoryProvider->get(Product::class)->findById($productId);
        if (null !== $product) {
            $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
            /** @var CartItem|null $cartItem */
            $cartItem = $cartItemRepository->findOneBy(['cartId' => $cart->id, 'productId' => $product->id]);
            if (null === $cartItem) {
                if ($amount > 0) {
                    $cartItem = CartItem::create($cart->id, $product->id, $product->userId, $amount);
                    $cartItemRepository->create($cartItem);
                }
            } else {
                if ($amount === 0) {
                    $cartItemRepository->delete($cartItem, true);
                } else {
                    $cartItem->amount = $amount;
                    $cartItemRepository->update($cartItem, ['amount']);
                }
            }
        }
    }

    /** @throws TokenNotFoundException */
    public function getTokenByHeader(): Token
    {
        $tokenCode = $this->requestStack->getCurrentRequest()->headers->get(CartManager::TOKEN_HEADER_NAME, '');
        $token = $this->tokenProvider->getByCode($tokenCode, OrderOnlyToken::TYPE_ID);

        return $token;
    }

    public function findOrderOnlyCartByHeader(): ?Cart
    {
        try {
            $token = $this->getTokenByHeader();
        } catch (TokenNotFoundException $e) {
            return null;
        }
        /** @var Cart $cart */
        $cart = $this->repositoryProvider->get(Cart::class)->findById($token->data[OrderOnlyToken::DATA_KEY]);
//        if (Cart::STATUS_ID_PAID !== $cart->statusId) {
//            return null;
//        }

        return $cart;
    }

//    public function compileJsData(): ?array
//    {
//        $cart = $this->findCart();
//        if (null === $cart) {
//            return null;
//        }
//        $data = [];
//
//        return $data;
//    }
    /**
     * @param string $ip
     * @return Cart
     *
     * There is an external transaction
     */
    private function initCart(string $ip): Cart
    {
        $ipInstance = $this->repositoryProvider->get(Ip::class)->findOneBy(['ip' => $ip]);
        $this->defaultDbClient->beginTransaction();
        try {
            if (null === $ipInstance) {
                $ipInstance = Ip::create($ip);
                $this->repositoryProvider->get(Ip::class)->create($ipInstance);
            }
            $cart = Cart::create($ipInstance->id);
            $this->repositoryProvider->get(Cart::class)->create($cart);
            try {
                $tokenData = [CartToken::DATA_KEY => $cart->id];
                $token = $this->tokenProvider->generate(CartToken::class, $tokenData, $ipInstance->ip);
            } catch (ActiveTokenExistsException $e) {
                throw new LogicException('', 0, $e);
            }
            $this->setCookie($token, CartToken::LIFE_TIME * 60);
        } catch (Exception $exception) {
            $this->defaultDbClient->rollback();
            throw $exception;
        }
        $this->defaultDbClient->commit();

        return $cart;
    }

    private function findNotFixedCart(): ?Cart
    {
        $tokenCode = $this->requestStack->getCurrentRequest()->cookies->get(self::COOKIE_NAME);
        if (null === $tokenCode) {
            return null;
        }
        try {
            $token = $this->tokenProvider->getByCode($tokenCode, CartToken::TYPE_ID);
        } catch (TokenNotFoundException $e) {
            return null;
        }
        $cart = $this->repositoryProvider->get(Cart::class)->findOneBy(
            ['id' => $token->data[CartToken::DATA_KEY], 'statusId' => Cart::STATUS_ID_NEW]
        );
        if (null === $cart) {
            return null;
        }
        $this->setCookie($token, CartToken::LIFE_TIME * 60);

        return $cart;
    }

    private function setCookie(Token $token, $duration): void
    {
        $value = $this->tokenProvider->compileTokenCode($token);
        SetCookie(self::COOKIE_NAME, $value, time() + $duration, '/', $this->domain, true, true);
    }

    public function fixCart(Cart $cart, string $email, string $ip, string $locale): Token
    {
        $currencyId = Currency::ID_RUB;
        /** @var CartItemRepository $cartItemRepository */
        $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
        $cartRepository = $this->repositoryProvider->get(Cart::class);
        $productRepository = $this->repositoryProvider->get(Product::class);
        $customerRepository = $this->repositoryProvider->get(Customer::class);

        /** @var Customer|null $customer */
        $customer = $customerRepository->findOneBy(['email' => $email]);
        $partner = $this->partnerManager->getRequestPartner();

        $this->defaultDbClient->beginTransaction();
        try {
            if (null === $customer) {
                $customer = Customer::create($email);
                $customerRepository->create($customer);
            }
            $cartItemRepository->deleteByCart($cart);
            $cartItemsFieldName = Cart::DYNAMICAL_FIELD_CART_ITEMS;
            /** @var CartItem[] $cartItems */
            $cartItems = $cart->$cartItemsFieldName;
            /** @var Product[] $productsIndexedById */
            $productsIndexedById = $productRepository->findByRelativeIndexed($cartItems);
            $totalProductsAmount = '0';
            foreach ($cartItems as $cartItem) {
                $product = $productsIndexedById[$cartItem->productId];
                $productPrice = $this->currencyConverter
                    ->convert($product->currencyId, $currencyId, $product->price);
                $cartItemPrice = bcmul($productPrice, $cartItem->amount, 2);
                $cartItem->customerId = $customer->id;
                $cartItem->cartId = $cart->id;
                $cartItem->currencyId = $currencyId;
                $cartItem->price = $cartItemPrice;
                $cartItem->productUserId = $product->userId;
                $cartItem->productPrice = $productPrice;
                if (null !== $partner && $product->userId !== $partner->id) {
                    $cartItem->partnerId = $partner->id;
                }
                $cartItemRepository->create($cartItem);
                $totalProductsAmount = bcadd($totalProductsAmount, $cartItemPrice, 2);
            }
            $cart->locale = $locale;
            $cart->statusId = Cart::STATUS_ID_FIXED;
            $cart->currencyId = $currencyId;
            $cart->totalProductsAmount = $totalProductsAmount;
            $cart->customerId = $customer->id;
            $cartRepository->update(
                $cart,
                ['customerId', 'statusId', 'currencyId', 'email', 'totalProductsAmount', 'locale']
            );
            $token = $this->generateOrderOnlyToken($cart, $ip);
            $this->defaultDbClient->commit();

            return $token;
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }
    }

    public function generateOrderOnlyToken(Cart $cart, string $ip = null): Token
    {
        try {
            $tokenData = [CartToken::DATA_KEY => $cart->id];
            $token = $this->tokenProvider->generate(OrderOnlyToken::class, $tokenData, $ip);

            return $token;
        } catch (ActiveTokenExistsException $e) {
            throw new LogicException('', 0, $e);
        }
    }

    public function compileOrderOnlyUrl(Cart $cart, Token $token): string
    {
        $tokenKey = $this->tokenProvider->compileTokenCode($token);
        $url = "https://{$this->domainCustomer}/order-only/{$cart->id}/$tokenKey";

        return $url;
    }
}
