<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;
use Symfony\Contracts\Translation\TranslatorInterface;

class Account
{
    const FIELD_USER_ID = 'userId';

    const DEFAULT_CURRENCY_ID = Currency::ID_RUB;

    const NO_LAST_TRANSACTION_ID = 0;

    /** @Db\BigIntType */
    public $id;
    /** @Db\IntType */
    public $userId;
    /** @Db\IntType */
    public $currencyId;
    /** @Db\DecimalType */
    public $balance = '0';
    /** @Db\DecimalType */
    public $hold = '0';
    /** @Db\BigIntType */
    public $lastTransactionId = self::NO_LAST_TRANSACTION_ID;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create(
        $userId,
        $currencyId
    ): self {
        $item = new self();
        $item->userId = $userId;
        $item->currencyId = $currencyId;

        return $item;
    }

    public function compileAdminApiView(Currency $currency): array
    {
        $view = [
            'id' => $this->id,
            'balance' => $this->balance,
            'currency' => $currency->name,
        ];

        return $view;
    }

    public function compilePrivateBalanceView(TranslatorInterface $translator)
    {
        return [
            'id' => $this->id,
            'balance' => number_format($this->balance, 2, '.', ','),
            'currencyView' => $translator->trans("currency.$this->currencyId.sign", [], 'payment'),
            'hold' => number_format($this->hold, 2, '.', ','),
        ];
    }
}
