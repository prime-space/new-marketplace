<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Ip
{
    const FIELD_ID = 'id';
    const FIELD_IP = 'ip';
    const FIELD_IS_DELETED = 'isDeleted';
    const FIELD_CREATED_TS = 'createdTs';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\VarcharType(length = 39) */
    public $ip;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create(string $ip): self
    {
        $item = new self();

        $item->ip = $ip;

        return $item;
    }

    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }
}
