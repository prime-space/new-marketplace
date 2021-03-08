<?php namespace App\Telegram;

use App\MessageBroker\MessageBrokerConfig;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class TelegramSender
{
    private const TELEGRAM_API_URL = 'https://api.telegram.org';

    private $domain;
    private $messageBroker;
    private $guzzleClient;
    private $chatId;
    private $botToken;

    public function __construct(
        string $domain,
        MessageBroker $messageBroker,
        string $chatId,
        string $botToken
    ) {
        $this->domain = $domain;
        $this->messageBroker = $messageBroker;
        $this->guzzleClient = new GuzzleClient();
        $this->chatId = $chatId;
        $this->botToken = $botToken;
    }

    public function send(string $message): void
    {
        $compiledMessage = sprintf("Host: %s\nInfo: %s", $this->domain, $message);
        $this->messageBroker->createMessage(MessageBrokerConfig::QUEUE_NAME_TELEGRAM_MESSAGE, [
            'text' => $compiledMessage,
        ]);
    }

    /** @throws RequestException */
    public function doRequest(string $message): void
    {
        $data = ['chat_id' => $this->chatId, 'text' => $message];
        $query = http_build_query($data);
        $url = sprintf('%s/bot%s/sendMessage?%s', self::TELEGRAM_API_URL, $this->botToken, $query);

        $options = [
            'timeout' => 6,
            'connect_timeout' => 6,
        ];
        $this->guzzleClient->post($url, $options);
    }
}
