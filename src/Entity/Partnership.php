<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Partnership
{
    const STATUS_ID_OFFER = 1;
    const STATUS_ID_REJECTED = 2;
    const STATUS_ID_OK = 3;

    /** @Db\BigIntType */
    public $id;
    /** @Db\BigIntType */
    public $sellerUserId;
    /** @Db\BigIntType */
    public $agentUserId;
    /** @Db\TinyIntType() */
    public $statusId = self::STATUS_ID_OFFER;
    /** @Db\DecimalType */
    public $fee;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create($sellerUserId, $agentUserId, $fee): self
    {
        $item = new self();
        $item->sellerUserId = $sellerUserId;
        $item->agentUserId = $agentUserId;
        $item->fee = $fee;

        return $item;
    }
}
