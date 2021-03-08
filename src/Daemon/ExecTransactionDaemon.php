<?php namespace App\Daemon;

use App\Account\Accountant;
use App\Entity\Transaction;
use App\MessageBroker\MessageBrokerConfig;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\MysqlMessageBrokerBundle\AbstractDaemon;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use RuntimeException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecTransactionDaemon extends AbstractDaemon
{
    private $messageBroker;
    private $defaultDbClient;
    private $accountant;

    public function __construct(
        Logger $logger,
        MessageBroker $messageBroker,
        DbClient $defaultDbClient,
        Accountant $accountant
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
        $this->defaultDbClient = $defaultDbClient;
        $this->accountant = $accountant;
    }

    /** @inheritdoc */
    protected function do(InputInterface $input, OutputInterface $output)
    {
        $message = $this->messageBroker->getMessage(MessageBrokerConfig::QUEUE_NAME_EXEC_TRANSACTION);
        $this->logExtraDataKeeper->setParam('id', $message['id']);
        $this->logger->info('Execute transaction');

        /** @var Transaction $transaction */
        $transaction = $this->repositoryProvider->get(Transaction::class)->findById($message['id']);
        if (null === $transaction) {
            throw new RuntimeException('Transaction not found');
        }
        if ($transaction->isExecuted()) {
            $this->logger->error('Already executed');

            return 0;
        }

        $this->accountant->executeTransaction($transaction);

        $this->logger->info('Success');

        return 0;
    }
}
