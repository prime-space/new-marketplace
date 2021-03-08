<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Customer
{
    const FIELD_EMAIL = 'email';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\VarcharType(length = 64) */
    public $email;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create($email): self
    {
        $item = new self();
        $item->email = $email;

        return $item;
    }
}
