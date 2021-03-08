<?php namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Transaction;
use Ewll\DBBundle\Repository\Repository;

class AccountRepository extends Repository
{
    const HOLD_INCREASE = 1;
    const HOLD_DECREASE = 2;

    public function addHoldByTransaction(Transaction $transaction, int $action): void
    {
        switch ($action) {
            case self::HOLD_INCREASE:
                $amount = $transaction->amount;
                break;
            case self::HOLD_DECREASE:
                $amount = bcmul($transaction->amount, '-1', Currency::MAX_SCALE);
                break;
            default:
                throw new \RuntimeException('Unknown action');
        }
        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE account
SET hold = hold + :amount
WHERE
    id = :accountId
SQL
            )
            ->execute(['accountId' => $transaction->accountId, 'amount' => $amount]);
    }
}
