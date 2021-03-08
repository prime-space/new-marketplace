<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Country
{
    const COUNTRY_ID_GB = 826;

    /** @Db\BigIntType() */
    public $id;
    /** @Db\VarcharType(length = 250) */
    public $name;
    /** @Db\VarcharType(length = 250) */
    public $fullname;
    /** @Db\VarcharType(length = 2) */
    public $alpha2;
    /** @Db\VarcharType(length = 3) */
    public $alpha3;
    /** @Db\TinyIntType */
    public $localeId;
    /** @Db\TinyIntType */
    public $currencyId;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}
