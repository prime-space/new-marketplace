<?php namespace App\Controller;

use App\Entity\Cart;
use App\MessageBroker\MessageBrokerConfig;
use App\Payment\Exception\CheckPaymentException;
use App\Payment\PrimepayerPaymentSystem;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalController extends AbstractController
{
    private $primepayerPaymentSystem;
    private $repositoryProvider;
    private $messageBroker;

    public function __construct(
        PrimepayerPaymentSystem $primepayerPaymentSystem,
        RepositoryProvider $repositoryProvider,
        MessageBroker $messageBroker
    ) {
        $this->primepayerPaymentSystem = $primepayerPaymentSystem;
        $this->repositoryProvider = $repositoryProvider;
        $this->messageBroker = $messageBroker;
    }

    public function paymentResult(Request $request)
    {
        $logger = $this->primepayerPaymentSystem->getLogger();
        $logger->info('Incomming result', [$request->request->all(), $request->query->all()]);
        /** @TODO PaymentShot*/
        $orderId = $this->primepayerPaymentSystem->getOrderIdFromResultRequest($request);
        /** @var Cart $cart */
        $cart = $this->repositoryProvider->get(Cart::class)->findById($orderId);
        if (null === $cart) {
            $message = 'Order not found';
            $logger->error($message, ['orderId' => $orderId]);

            return new Response($message, 404);
        }
        try {
            $this->primepayerPaymentSystem->check($request, $cart->currencyId, $cart->totalProductsAmount);
        } catch (CheckPaymentException $e) {
            $logger->error("#{$orderId} {$e->getMessage()}");

            return new Response('', 400);
        }

        $this->messageBroker->createMessage(MessageBrokerConfig::QUEUE_NAME_EXEC_ORDER, [
            'orderId' => $orderId
        ]);
        $logger->info("#{$orderId} moved to execution");

        return new Response();
    }
}
