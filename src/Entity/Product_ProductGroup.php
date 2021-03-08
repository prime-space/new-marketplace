<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Product_ProductGroup
{
    const FIELD_PRODUCT_ID = 'productId';
    const FIELD_PRODUCT_GROUP_ID = 'productGroupId';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $productId;
    /** @Db\BigIntType() */
    public $productGroupId;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}

