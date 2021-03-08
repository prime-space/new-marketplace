<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class CustomerBlockedEntity
{
    const TYPE_ID_EMAIL = 1;
    const TYPE_ID_IP = 2;

    const FIELD_ID = 'id';
    const FIELD_BLOCKED_BY_USER_ID = 'blockedByUserId';
    const FIELD_ENTITY_TYPE = 'entityTypeId';
    const FIELD_ENTITY_ID = 'entityId';
    const FIELD_IS_DELETED = 'isDeleted';
    const FIELD_CREATED_TS = 'createdTs';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $blockedByUserId;
    /** @Db\TinyIntType (1) */
    public $entityTypeId;
    /** @Db\BigIntType() */
    public $entityId;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}
