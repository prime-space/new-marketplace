<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class PayoutMethod
{
    const ID_QIWI = 1;
    const ID_YANDEX = 2;

    /** @Db\IntType */
    public $id;
    /** @Db\DecimalType */
    public $fee;
    /** @Db\VarcharType(length = 64) */
    public $name;
    /** @Db\BoolType */
    public $isEnabled;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

}
