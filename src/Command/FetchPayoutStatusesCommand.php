<?php namespace App\Command;

use App\Account\Accountant;
use App\Entity\Event;
use App\Entity\Payout;
use App\Factory\EventFactory;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Query\QueryBuilder;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\Repository;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchPayoutStatusesCommand extends AbstractCommand
{
    const EXTERNAL_STATUS_ID_SUCCESS = 3;
    const EXTERNAL_STATUS_ID_FAIL = 4;

    private $guzzleClient;
    private $primepayerDomain;
    private $primepayerUserId;
    private $primepayerApiKey;
    private $defaultDbClient;
    private $accountant;
    private $eventFactory;

    public function __construct(
        LoggerInterface $logger,
        string $primepayerDomain,
        string $primepayerUserId,
        string $primepayerApiKey,
        DbClient $defaultDbClient,
        Accountant $accountant,
        EventFactory $eventFactory
    ) {
        parent::__construct();

        $this->guzzleClient = new GuzzleClient();
        $this->logger = $logger;
        $this->primepayerDomain = $primepayerDomain;
        $this->primepayerUserId = $primepayerUserId;
        $this->primepayerApiKey = $primepayerApiKey;
        $this->defaultDbClient = $defaultDbClient;
        $this->accountant = $accountant;
        $this->eventFactory = $eventFactory;
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $statusMap = [
            self::EXTERNAL_STATUS_ID_SUCCESS => Payout::STATUS_ID_SUCCESS,
            self::EXTERNAL_STATUS_ID_FAIL => Payout::STATUS_ID_FAIL,
        ];
        $payoutRepository = $this->repositoryProvider->get(Payout::class);
        $oldestPayoutForChecking = $this->findOldestPayoutForChecking($payoutRepository);
        if (null === $oldestPayoutForChecking) {
            $this->logger->info('No one payout wait checking');

            return 0;
        }
        $this->logger->info("oldestPayoutForChecking #$oldestPayoutForChecking->id");

        try {
            $fromOperationId = $oldestPayoutForChecking->externalId;
            while (1) {
                $operations = $this->requestPayoutStatuses($fromOperationId);
                $operationsNum = count($operations);
                $this->logger->info("Recieved $operationsNum operations");
                foreach ($operations as $operation) {
                    $resultingExternalStatuses = [self::EXTERNAL_STATUS_ID_SUCCESS, self::EXTERNAL_STATUS_ID_FAIL];
                    if (in_array($operation['statusId'], $resultingExternalStatuses, true)) {
                        $payout = $payoutRepository->findOneBy([Payout::FIELD_EXTERNAL_ID => $operation['id']]);
                        if (null !== $payout && $payout->statusId === Payout::STATUS_ID_CHECKING) {
                            $this->updateStatus($payoutRepository, $payout, $statusMap[$operation['statusId']]);
                        }
                    }
                }
                if ($operationsNum === 100) {
                    $fromOperationId = $operation['id'];
                } else {
                    break;
                }
            }
        } catch (RequestException $e) {
            $this->logger->error("RequestException: {$e->getMessage()}");
        }

        return 0;
    }

    /** @throws RequestException */
    private function requestPayoutStatuses($fromOperationId): array
    {
        $request = $this->guzzleClient->post(
            "https://{$this->primepayerDomain}/api/{$this->primepayerUserId}/payouts",
            [
                'timeout' => 15,
                'connect_timeout' => 15,
                'headers' => [
                    'Authorization' => "Bearer {$this->primepayerApiKey}",
                ],
                'form_params' => [
                    'fromOperationId' => $fromOperationId,
                ]
            ]
        );
        $result = json_decode($request->getBody()->getContents(), true);

        return $result['operations'];
    }

    private function findOldestPayoutForChecking(Repository $payoutRepository): ?Payout
    {
        $qb = new QueryBuilder($payoutRepository);
        $qb
            ->addCondition(new FilterExpression(FilterExpression::ACTION_IS_NOT_NULL, 'externalId'))
            ->setSort([[
                'type' => Repository::SORT_TYPE_SIMPLE,
                'field' => 'id',
                'method' => 'asc',
            ]])
            ->setLimit(1);

        return $payoutRepository->find($qb);
    }

    private function updateStatus(Repository $payoutRepository, Payout $payout, int $statusId): void
    {
        $payout->statusId = $statusId;
        $this->defaultDbClient->beginTransaction();
        try {
            $payoutRepository->update($payout, ['statusId']);
            if ($payout->statusId === Payout::STATUS_ID_FAIL) {
                $this->accountant->refundPayout($payout);
                $this->eventFactory->create(
                    $payout->userId,
                    Event::TYPE_ID_UNSUCCESSFUL_PAYOUT,
                    $payout->id
                );
            }
            $this->defaultDbClient->commit();
        } catch (\Exception $e) {
            $this->defaultDbClient->rollback();

            throw new \RuntimeException("Transaction fail: {$e->getMessage()}", 0, $e);
        }
    }
}
