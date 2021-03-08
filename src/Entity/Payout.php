<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Payout
{
    const FIELD_EXTERNAL_ID = 'externalId';

    const STATUS_ID_QUEUE = 1;
    const STATUS_ID_PROCESS = 2;
    const STATUS_ID_CHECKING = 3;
    const STATUS_ID_SUCCESS = 4;
    const STATUS_ID_FAIL = 5;
    const STATUS_ID_UNKNOWN = 6;

    /** @Db\BigIntType */
    public $id;
    /** @Db\BigIntType */
    public $externalId;
    /** @Db\IntType */
    public $accountId;
    /** @Db\IntType */
    public $payoutMethodId;
    /** @Db\IntType */
    public $userId;
    /** @Db\VarcharType(length = 64) */
    public $receiver;
    /** @Db\DecimalType */
    public $amount;
    /** @Db\DecimalType */
    public $fee;
    /** @Db\DecimalType */
    public $writeOff;
    /** @Db\TinyIntType */
    public $statusId = 1;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}
