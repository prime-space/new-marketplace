<?php namespace App\Daemon;

use App\MessageBroker\MessageBrokerConfig;
use App\Telegram\TelegramSender;
use Ewll\MysqlMessageBrokerBundle\AbstractDaemon;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TelegramNotificationDaemon extends AbstractDaemon
{
    private $messageBroker;
    private $telegramSender;

    public function __construct(
        Logger $logger,
        MessageBroker $messageBroker,
        TelegramSender $telegramSender
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
        $this->telegramSender = $telegramSender;
    }

    /** @throws RequestException */
    protected function do(InputInterface $input, OutputInterface $output)
    {
        $message = $this->messageBroker->getMessage(MessageBrokerConfig::QUEUE_NAME_TELEGRAM_MESSAGE);
        $this->logger->info("Send notification. Text - {$message['text']}");
        $this->telegramSender->doRequest($message['text']);
        $this->logger->info('Success');

        return 0;
    }
}
