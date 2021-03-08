<?php namespace App\Payout\Item;

use App\Crud\Unit\Payout\Form\Constraint\PayoutReceiver;
use App\Entity\PayoutMethod;
use App\Payout\Exception\PayoutReceiverValidationException;
use App\Payout\PayoutMethodManagerInterface;

class YandexPayoutMethodManager implements PayoutMethodManagerInterface
{
    public function getMethodId(): int
    {
        return PayoutMethod::ID_YANDEX;
    }

    /** @inheritDoc */
    public function validateReceiver(string $receiver): void
    {
        if (preg_match('/^41001[0-9]{8,11}$/', $receiver) !== 1) {
            throw new PayoutReceiverValidationException(PayoutReceiver::MESSAGE_KEY_INCORRECT);
        }
    }
}
