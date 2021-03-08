<?php namespace App\Payout\Item;

use App\Crud\Unit\Payout\Form\Constraint\PayoutReceiver;
use App\Entity\PayoutMethod;
use App\Payout\Exception\PayoutReceiverValidationException;
use App\Payout\PayoutMethodManagerInterface;

class QiwiPayoutMethodManager implements PayoutMethodManagerInterface
{
    public function getMethodId(): int
    {
        return PayoutMethod::ID_QIWI;
    }

    /** @inheritDoc */
    public function validateReceiver(string $receiver): void
    {
        if (preg_match('/^\+[1-9]{1}[0-9]{3,14}$/', $receiver) !== 1) {
            throw new PayoutReceiverValidationException(PayoutReceiver::MESSAGE_KEY_INCORRECT);
        }
    }
}
