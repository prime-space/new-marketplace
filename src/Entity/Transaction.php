<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Transaction
{
    /** @Db\BigIntType */
    public $id;
    /** @Db\IntType */
    public $userId;
    /** @Db\IntType */
    public $accountId;
    /** @Db\VarcharType(length = 32) */
    public $methodId;
    /** @Db\JsonType */
    public $descriptionData;
    /** @Db\DecimalType */
    public $amount;
    /** @Db\IntType */
    public $currencyId;
    /** @Db\DecimalType */
    public $balance;
    /** @Db\BigIntType */
    public $accountOperationId;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $executingTs;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create(
        $userId,
        $accountId,
        $methodId,
        $descriptionData,
        $amount,
        $currencyId,
        $executingTs = null
    ): self {
        $item = new self();
        $item->userId = $userId;
        $item->accountId = $accountId;
        $item->methodId = $methodId;
        $item->descriptionData = $descriptionData;
        $item->amount = $amount;
        $item->currencyId = $currencyId;
        $item->executingTs = $executingTs;

        return $item;
    }

    public function isExecuted()
    {
        return $this->accountOperationId !== null;
    }
}
