<?php namespace App\Payment;

use App\Payment\Exception\CheckPaymentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PrimepayerPaymentSystem
{
    const SERVER_IPS = ['109.120.152.109', '145.239.84.249'];
    const CURRENCY_RUB = 3;

    private $shopId;
    private $shopSecret;
    private $logger;

    public function __construct(int $shopId, string $shopSecret, LoggerInterface $logger)
    {
        $this->shopId = $shopId;
        $this->shopSecret = $shopSecret;
        $this->logger = $logger;
    }

    public function init(
        int $paymentId,
        string $amount,
        string $description,
        int $currencyId,
        string $email,
        string $successUrl
    ): FormData {
        $fields = [
            'shop' => $this->shopId,
            'payment' => $paymentId,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currencyId,
            'email' => $email,
            'success' => $successUrl,
        ];

        ksort($fields, SORT_STRING);
        $fields['sign'] = hash('sha256', implode(':', $fields) . ':' . $this->shopSecret);

        $formData = new FormData('https://primepayer.com/pay', FormData::METHOD_POST, $fields);

        return $formData;
    }

    /** @throws CheckPaymentException */
    public function check(Request $request, int $realCurrency, string $realAmount)
    {
        $ip = $request->getClientIp();
        if (!in_array($ip, self::SERVER_IPS, true)) {
            throw new CheckPaymentException("Wrong IP: '$ip'");
        }

        $data = $request->request->all();
        $sign = $request->request->get('sign');
        unset($data['sign']);
        ksort($data, SORT_STRING);
        $realSign = hash('sha256', sprintf('%s:%s', implode(':', $data), $this->shopSecret));
        if ($sign !== $realSign) {
            throw new CheckPaymentException("Wrong sign");
        }

        $currency = $request->request->getInt('currency');
        if ($currency !== $realCurrency) {
            throw new CheckPaymentException("Wrong currency: $currency, expect: $realCurrency");
        }

        $amount = $request->request->get('amount');
        if (1 === bccomp($realAmount, $amount, 8)) {
            throw new CheckPaymentException("Wrong amount: $amount, expect: $realAmount");
        }
    }

    /** @return LoggerInterface */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getOrderIdFromResultRequest(Request $request): int
    {
        return $request->request->getInt('payment');
    }
}
