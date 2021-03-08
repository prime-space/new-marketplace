<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Currency
{
    const ID_USD = 1;
    const ID_EUR = 2;
    const ID_RUB = 3;
    const ID_UAH = 4;
    const ID_BTC = 5;

    const MAX_SCALE = 8;

    /** @Db\IntType */
    public $id;
    /** @Db\VarcharType(length = 32) */
    public $name;
    /** @Db\DecimalType */
    public $rate;
    /** @Db\TinyIntType */
    public $scale;
}
