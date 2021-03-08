<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Tariff
{
    const FIELD_ID = 'id';
    const FIELD_PRICE = 'price';

    const ID_SELLER = 1;

    /** @Db\BigIntType */
    public $id;
    /** @Db\VarcharType(length = 64) */
    public $name;
    /** @Db\DecimalType */
    public $saleFee;
    /** @Db\IntType() */
    public $holdSeconds;
    /** @Db\DecimalType */
    public $price;
    /** @Db\VarcharType(length = 32) */
    public $icon;
    /** @Db\BoolType() */
    public $isHidden = false;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}
