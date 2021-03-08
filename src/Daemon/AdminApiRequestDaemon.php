<?php namespace App\Daemon;

use App\Api\Item\Admin\Handler\AdminApiHandlerInterface;
use App\MessageBroker\MessageBrokerConfig;
use Ewll\MysqlMessageBrokerBundle\AbstractDaemon;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminApiRequestDaemon extends AbstractDaemon
{
    private $messageBroker;
    /** @var AdminApiHandlerInterface[] */
    private $adminApiHandlers;

    public function __construct(
        Logger $logger,
        MessageBroker $messageBroker,
        iterable $adminApiHandlers
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
        $this->adminApiHandlers = $adminApiHandlers;
    }

    /** @throws RequestException */
    protected function do(InputInterface $input, OutputInterface $output)
    {
        $message = $this->messageBroker->getMessage(MessageBrokerConfig::QUEUE_NAME_ADMIN_API_REQUEST);
        $this->logger->info('Request', $message);

        $service = $this->getAdminApiHandler($message['serviveClass']);
        call_user_func_array([$service, 'inverse'.ucfirst($message['method'])], $message['args']);

        $this->logger->info('Success');

        return 0;
    }

    private function getAdminApiHandler(string $className): AdminApiHandlerInterface
    {
        foreach ($this->adminApiHandlers as $adminApiHandler) {
            if ($adminApiHandler instanceof $className) {
                return $adminApiHandler;
            }
        }

        throw new \RuntimeException("AdminApiHandler '$className' not found");
    }
}
