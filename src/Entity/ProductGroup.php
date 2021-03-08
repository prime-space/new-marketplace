<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class ProductGroup
{
    const FIELD_ID = 'id';
    const FIELD_USER_ID = 'userId';
    const FIELD_NAME = 'name';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $userId;
    /** @Db\VarcharType() */
    public $name;
    /** @Db\IntType() */
    public $productsNum = 0;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}

