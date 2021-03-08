<?php namespace App\Daemon;

use App\Entity\Payout;
use App\Entity\PayoutMethod;
use App\MessageBroker\MessageBrokerConfig;
use App\Telegram\TelegramSender;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\MysqlMessageBrokerBundle\AbstractDaemon;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class SendPayoutDaemon extends AbstractDaemon
{
    private $messageBroker;
    private $defaultDbClient;
    private $guzzle;
    private $primepayerDomain;
    private $primepayerUserId;
    private $primepayerApiKey;
    private $telegramSender;

    public function __construct(
        Logger $logger,
        MessageBroker $messageBroker,
        DbClient $defaultDbClient,
        string $primepayerDomain,
        int $primepayerUserId,
        string $primepayerApiKey,
        TelegramSender $telegramSender
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
        $this->defaultDbClient = $defaultDbClient;
        $this->guzzle = new GuzzleClient();
        $this->primepayerDomain = $primepayerDomain;
        $this->primepayerUserId = $primepayerUserId;
        $this->primepayerApiKey = $primepayerApiKey;
        $this->telegramSender = $telegramSender;
    }

    /** @inheritdoc */
    protected function do(InputInterface $input, OutputInterface $output)
    {
        $message = $this->messageBroker->getMessage(MessageBrokerConfig::QUEUE_NAME_SEND_PAYOUT);
        $this->logExtraDataKeeper->setParam('id', $message['id']);
        $this->logger->info("Send payout, attempt {$message['attempt']}");

        $payoutRepository = $this->repositoryProvider->get(Payout::class);

        /** @var Payout $payout */
        $payout = $payoutRepository->findById($message['id']);
        if (null === $payout) {
            throw new RuntimeException('Transaction not found');
        }
        if ($payout->statusId !== Payout::STATUS_ID_QUEUE) {
            $this->logger->error("Status $payout->statusId, expect " . Payout::STATUS_ID_QUEUE);

            return 0;
        }

        $methodMap = [
            PayoutMethod::ID_QIWI => 'qiwi',
            PayoutMethod::ID_YANDEX => 'yandex',
        ];

        try {
            $request = $this->guzzle->post(
                "https://{$this->primepayerDomain}/api/{$this->primepayerUserId}/payout",
                [
                    'timeout' => 15,
                    'connect_timeout' => 15,
                    'headers' => [
                        'Authorization' => "Bearer {$this->primepayerApiKey}",
                    ],
                    'form_params' => [
                        'id' => $payout->id,
                        'receiver' => $payout->receiver,
                        'method' => $methodMap[$payout->payoutMethodId],
                        'accountId' => 4,
                        'amount' => $payout->amount,
                    ]
                ]
            );
            $result = json_decode($request->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $message['attempt']++;
            $delay = $message['attempt'] < 60 ? $message['attempt'] * 10 : 600;
            $this->logger->error("RequestException: {$e->getMessage()}");
            $response = $e->getResponse();
            if (null === $response) {
                $this->logger->error("No response, retry in $delay seconds");
                $this->messageBroker->createMessage(MessageBrokerConfig::QUEUE_NAME_SEND_PAYOUT, $message, $delay);

                return 0;
            }
            $responseStatusCode = $response->getStatusCode();
            if ($responseStatusCode === Response::HTTP_BAD_REQUEST) {
                $errors = json_decode($response->getBody()->getContents(), true);
                $this->logger->error('HTTP_BAD_REQUEST, move to unknown status', $errors);
                $this->moveToUnknownStatus($payout, $errors);
            } else {
                $this->logger->error("ResponseStatusCode: $responseStatusCode, retry in $delay seconds");
                $this->messageBroker->createMessage(MessageBrokerConfig::QUEUE_NAME_SEND_PAYOUT, $message, $delay);
            }

            return 0;
        }
        if (!isset($result['operationId'])) {
            throw new RuntimeException('result isn\'t content operationId');
        }
        $payout->externalId = $result['operationId'];
        $payout->statusId = Payout::STATUS_ID_CHECKING;
        $payoutRepository->update($payout, ['externalId', 'statusId']);

        $this->logger->info('Sent');

        return 0;
    }

    private function moveToUnknownStatus(Payout $payout, array $reason): void
    {
        $payoutRepository = $this->repositoryProvider->get(Payout::class);
        $this->defaultDbClient->beginTransaction();
        try {
            $payout->statusId = Payout::STATUS_ID_UNKNOWN;
            $payoutRepository->update($payout, ['statusId']);
            $message = sprintf(
                "Payout #%s got unknown status.\n%s",
                $payout->id,
                json_encode($reason, JSON_UNESCAPED_UNICODE),
            );
            $this->telegramSender->send($message);
            $this->defaultDbClient->commit();
        } catch (\Exception $e) {
            $this->defaultDbClient->rollback();

            throw new RuntimeException("Transaction fail: {$e->getMessage()}", 0, $e);
        }
    }
}
