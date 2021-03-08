<?php namespace App\Daemon;

use App\Entity\Product;
use App\MessageBroker\MessageBrokerConfig;
use App\Repository\ProductRepository;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\MysqlMessageBrokerBundle\AbstractDaemon;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SalesUpDaemon extends AbstractDaemon
{
    const METHOD_UP = 'up';
    const METHOD_DOWN = 'down';

    private $messageBroker;
    private $defaultDbClient;

    public function __construct(
        Logger $logger,
        MessageBroker $messageBroker,
        DbClient $defaultDbClient
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
        $this->defaultDbClient = $defaultDbClient;
    }

    /** @throws RequestException */
    protected function do(InputInterface $input, OutputInterface $output)
    {
        $message = $this->messageBroker->getMessage(MessageBrokerConfig::QUEUE_NAME_SALES_UP);
        $this->logger
            ->info("Product #{$message['id']}, method '{$message['method']}', cycle {$message['cycle']} days");
        /** @var ProductRepository $productRepository */
        $productRepository = $this->repositoryProvider->get(Product::class);
        /** @var Product|null $product */
        $product = $productRepository->findById($message['id']);
        if (null === $product) {
            throw new \RuntimeException('Product not found');
        }

        switch ($message['method']) {
            case self::METHOD_UP:
                $this->defaultDbClient->beginTransaction();
                try {
                    $productRepository->salesUp($product);
                    $cycleSeconds = $message['cycle'] * 24 * 60 * 60;
                    $downMessage = $message;
                    $downMessage['method'] = self::METHOD_DOWN;
                    $this->messageBroker->createMessage(
                        MessageBrokerConfig::QUEUE_NAME_SALES_UP,
                        $downMessage,
                        $cycleSeconds
                    );
                    $this->defaultDbClient->commit();
                } catch (\Exception $e) {
                    $this->defaultDbClient->rollback();

                    throw new \RuntimeException("Transaction fail: {$e->getMessage()}", 0, $e);
                }
                break;
            case self::METHOD_DOWN:
                $productRepository->salesDown($product);
                break;
            default:
                throw new \RuntimeException('Unknown method');
        }
        $this->logger->info('Success');

        return 0;
    }
}
