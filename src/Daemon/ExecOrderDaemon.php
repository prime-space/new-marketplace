<?php namespace App\Daemon;

use App\Account\Accountant;
use App\Cart\CartManager;
use App\Controller\CustomerController;
use App\Controller\IndexController;
use App\Currency\CurrencyConverter;
use App\Entity\Account;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Currency;
use App\Entity\Customer;
use App\Entity\Event;
use App\Entity\Ip;
use App\Entity\Product;
use App\Entity\ProductObject;
use App\Entity\Tariff;
use App\Entity\User;
use App\Factory\EventFactory;
use App\MessageBroker\MessageBrokerConfig;
use App\Partner\PartnerManager;
use App\Product\ProductStatusResolver;
use App\Repository\ProductObjectRepository;
use DateTime;
use Ewll\DBBundle\Repository\Repository;
use Ewll\MailerBundle\Mailer;
use Ewll\MailerBundle\Template;
use Ewll\MysqlMessageBrokerBundle\AbstractDaemon;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Exception;
use Ewll\DBBundle\DB\Client as DbClient;
use RuntimeException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Loader\FilesystemLoader;

class ExecOrderDaemon extends AbstractDaemon
{
    const LETTER_NAME_ORDER_CUSTOMER = 'letterOrderCustomer';
    const LETTER_NAME_SALE_SELLER = 'letterSaleSeller';

    private $messageBroker;
    private $defaultDbClient;
    private $productStatusResolver;
    private $mailer;
    private $cartManager;
    private $router;
    private $domain;
    private $translator;
    private $accountant;
    private $currencyConverter;
    private $eventFactory;
    private $partnerManager;

    public function __construct(
        MessageBroker $messageBroker,
        Logger $logger,
        DbClient $defaultDbClient,
        ProductStatusResolver $productStatusResolver,
        Mailer $mailer,
        CartManager $cartManager,
        RouterInterface $router,
        string $domain,
        TranslatorInterface $translator,
        Accountant $accountant,
        CurrencyConverter $currencyConverter,
        EventFactory $eventFactory,
        PartnerManager $partnerManager
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
        $this->defaultDbClient = $defaultDbClient;
        $this->productStatusResolver = $productStatusResolver;
        $this->mailer = $mailer;
        $this->cartManager = $cartManager;
        $this->router = $router;
        $this->domain = $domain;
        $this->translator = $translator;
        $this->accountant = $accountant;
        $this->currencyConverter = $currencyConverter;
        $this->eventFactory = $eventFactory;
        $this->partnerManager = $partnerManager;
    }

    /** @inheritdoc */
    protected function do(InputInterface $input, OutputInterface $output)
    {
        bcscale(2);

        $message = $this->messageBroker->getMessage(MessageBrokerConfig::QUEUE_NAME_EXEC_ORDER);
        $cartId = $message['orderId'];

        $this->logExtraDataKeeper->setParam('cartId', $cartId);
        $this->logger->info('Handle order');

        $cartRepository = $this->repositoryProvider->get(Cart::class);
        $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
        $productRepository = $this->repositoryProvider->get(Product::class);
        $userRepository = $this->repositoryProvider->get(User::class);

        /** @var Cart $cart */
        $cart = $cartRepository->findById($cartId);
        if ($cart->statusId !== Cart::STATUS_ID_FIXED) {
            $this->logger->error("Status {$cart->statusId}, expected: " . Cart::STATUS_ID_FIXED);

            return 0;
        }
        $customer = $this->repositoryProvider->get(Customer::class)->findById($cart->customerId);
        $this->translator->setLocale($cart->locale);
        $cart->statusId = Cart::STATUS_ID_PAID;
        $cart->paidTs = new DateTime();

        /** @var CartItem[] $cartItems */
        $cartItems = $cartItemRepository->findBy(['cartId' => $cart->id]);

        $this->defaultDbClient->beginTransaction();
        try {
            /** @var Product[] $productsIndexedById */
            $productsIndexedById = $productRepository->findByRelativeIndexed($cartItems, null, Repository::FOR_UPDATE);
            /** @var User[] $sellersIndexedById */
            $sellersIndexedById = $userRepository->findByRelativeIndexed($productsIndexedById);
            $cartRepository->update($cart, ['statusId', 'paidTs']);
            $sellerIdsForNotification = [];
            foreach ($cartItems as $cartItem) {
                $product = $productsIndexedById[$cartItem->productId];
                $seller = $sellersIndexedById[$product->userId];
                $productObjects = $this->extractProductObjects($product, $cartItem);
                $cartItem->productObjectIds = array_column($productObjects, 'id');
                $cartItem->amountInFact = count($productObjects);
                $cartItem->isPaid = true;
                if (Product::TYPE_ID_UNIVERSAL !== $product->typeId) {
                    $product->inStockNum -= $cartItem->amountInFact;
                }
                if ($cartItem->amountInFact > 0) {
                    $cartItem->calculations = $this->distributeMoney($cart, $cartItem, $product, $seller);
                    $sellerIdsForNotification[] = $product->userId;
                    $this->createEventToSeller($product->userId, $cartItem->id);
                }
                // @TODO notification of out of stock
                $cartItemRepository->update($cartItem, ['productObjectIds', 'amountInFact', 'calculations', 'isPaid']);
            }
            $this->updateProducts($productRepository, $productsIndexedById);
            if (null !== $cart->customerIpId) {
                $ipInstance = $this->repositoryProvider->get(Ip::class)->findById($cart->customerIpId);
                $ip = $ipInstance->ip;
            } else {
                $ip = null;
            }
            $orderOnlyToken = $this->cartManager->generateOrderOnlyToken($cart, $ip);
            $orderOnlyUrl = $this->cartManager->compileOrderOnlyUrl($cart, $orderOnlyToken);
            $this->sendMailToCustomer($customer, $orderOnlyUrl);
            $sellerIdsForNotification = array_unique($sellerIdsForNotification);
            $this->sendMailsToSellers($sellerIdsForNotification);
            if (null !== $cartItem->partnerId) {//@TODO начисления может не быть, а уведомление появится
                $this->createEventToPartner($cartItem->partnerId);
            }
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }
        $this->logger->info('Success');

        return 0;
    }

    /** @param Product[] $products */
    private function updateProducts(Repository $productRepository, array $products)
    {
        $fieldNamesForUpdate = ['salesNum', 'inStockNum'];
        foreach ($products as $product) {
            if (Product::TYPE_ID_UNIQUE === $product->typeId) {
                $productStatusId = $this->productStatusResolver->resolve($product, ProductStatusResolver::ACTION_SALE);
                $product->statusId = $productStatusId;
                $fieldNamesForUpdate[] = 'statusId';
            }
            $product->salesNum++;
            $productRepository->update($product, $fieldNamesForUpdate);
        }
    }

    /** @return ProductObject[] */
    private function extractProductObjects(Product $product, CartItem $cartItem): array
    {
        /** @var ProductObjectRepository $productObjectRepository */
        $productObjectRepository = $this->repositoryProvider->get(ProductObject::class);
        switch ($product->typeId) {
            case Product::TYPE_ID_UNIVERSAL:
                $productObjects = [
                    $productObjectRepository->cloneUniversalForCartItem($product, $cartItem),
                ];
                break;
            case Product::TYPE_ID_UNIQUE:
                $productObjects = $productObjectRepository->extractToCartItem($product, $cartItem);
                break;
            default:
                throw new RuntimeException('Unknown product type' . $product->typeId);
        }

        return $productObjects;
    }

    private function sendMailToCustomer(Customer $customer, string $orderLink): void
    {
        $customerLink = 'https:' . $this->router->generate(
                CustomerController::ROUTE_CUSTOMER,
                [],
                UrlGeneratorInterface::NETWORK_PATH
            );
        $data = [
            'domain' => $this->domain,
            'customerLink' => $customerLink,
            'orderLink' => $orderLink,
        ];
        $template = new Template(self::LETTER_NAME_ORDER_CUSTOMER, FilesystemLoader::MAIN_NAMESPACE, $data);
        $this->mailer->create($customer->email, $template);
    }

    private function sendMailsToSellers(array $userIds): void
    {
        $detailsLink = 'https:' . $this->router->generate(
                IndexController::ROUTE_PRIVATE,
                [],
                UrlGeneratorInterface::NETWORK_PATH
            );
        $data = [
            'domain' => $this->domain,
            'detailsLink' => $detailsLink,
        ];
        foreach ($userIds as $userId) {
            /** @var User $user */
            $user = $this->repositoryProvider->get(User::class)->findById($userId);
            $template = new Template(self::LETTER_NAME_SALE_SELLER, FilesystemLoader::MAIN_NAMESPACE, $data);
            $this->mailer->createForUser($user, $template);
        }
    }

    private function createEventToPartner(int $partnerUserId): void
    {
        $this->eventFactory->create($partnerUserId, Event::TYPE_ID_SALE_PARTNER, 0, []);
    }

    private function createEventToSeller(int $userId, int $cartItemId): void
    {
        $this->eventFactory->create($userId, Event::TYPE_ID_SALE_SELLER, $cartItemId, []);
    }

    private function distributeMoney(Cart $cart, CartItem $cartItem, Product $product, User $seller): array
    {
        $calculations = [];
        $accountCurrencyId = Account::DEFAULT_CURRENCY_ID;//@TODO
        /** @var Tariff $sellerTariff */
        $sellerTariff = $this->repositoryProvider->get(Tariff::class)->findById($seller->tariffId);
        $convertedProductPrice = $this->currencyConverter
            ->convert($cart->currencyId, $accountCurrencyId, $cartItem->productPrice);
        $amount = bcmul($convertedProductPrice, $cartItem->amountInFact, 2);

        $calculations[] = $this->calcMoneyDistribution('systemFee', $amount, $sellerTariff->saleFee);

        if (null !== $cartItem->partnerId) {
            /** @var User $partner */
            $partner = $this->repositoryProvider->get(User::class)->findById($cartItem->partnerId);
            /** @var Tariff $partnerTariff */
            $partnerTariff = $this->repositoryProvider->get(Tariff::class)->findById($partner->tariffId);
            $partnerFee = $this->partnerManager->calcFee($partner, $product);
            $calculations[] = $this
                ->calcMoneyDistribution('partnerProfit', end($calculations)['leftAmount'], $partnerFee);
            $partnerProfit = end($calculations)['feeAmount'];
            if (1 === bccomp($partnerProfit, '0', 8)) {
                $descriptionData = [
                    'cartItemId' => $cartItem->id,
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'quantity' => $cartItem->amountInFact,
                    'sellerId' => $seller->id,
                    'sellerName' => $seller->getName(),
                ];
                $this->accountant->increase($partner->id, Accountant::METHOD_CART_ITEM_PARTNER, $descriptionData,
                    $partnerProfit, $accountCurrencyId, $partnerTariff->holdSeconds);
            }
        }

        $calculations[] = $this->calcMoneyDistribution('sellerProfit', end($calculations)['leftAmount']);
        $sellerProfit = end($calculations)['feeAmount'];
        if (1 === bccomp($sellerProfit, '0', 8)) {
            $descriptionData = [
                'cartItemId' => $cartItem->id,
                'productId' => $product->id,
                'productName' => $product->name,
                'quantity' => $cartItem->amountInFact
            ];
            $this->accountant->increase($product->userId, Accountant::METHOD_CART_ITEM_SELLER, $descriptionData,
                $sellerProfit, $accountCurrencyId, $sellerTariff->holdSeconds);
        }

        return $calculations;
    }

    private function calcMoneyDistribution(string $name, string $amount, string $part = '100'): array
    {
        $feeAmount = bcmul(bcdiv($part, '100', 4), $amount, Currency::MAX_SCALE);
        $leftAmount = bcsub($amount, $feeAmount, Currency::MAX_SCALE);
        return [
            'name' => $name,
            'amount' => $amount,
            'part' => $part,
            'feeAmount' => $feeAmount,
            'leftAmount' => $leftAmount,
        ];
    }
}
