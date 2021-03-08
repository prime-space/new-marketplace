<?php namespace App\Payout;

use App\Payout\Exception\PayoutReceiverValidationException;

interface PayoutMethodManagerInterface
{
    public function getMethodId(): int;
    /** @throws PayoutReceiverValidationException */
    public function validateReceiver(string $receiver): void;
}
