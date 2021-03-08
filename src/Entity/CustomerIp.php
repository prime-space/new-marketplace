<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class CustomerIp
{
    const FIELD_ID = 'id';
    const FIELD_CUSTOMER_ID = 'customerId';
    const FIELD_IP_ID = 'ipId';
    const FIELD_IS_DELETED = 'isDeleted';
    const FIELD_CREATED_TS = 'createdTs';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $customerId;
    /** @Db\BigIntType() */
    public $ipId;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}
