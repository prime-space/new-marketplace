<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Event
{
    const TYPE_ID_PRODUCT_VERIFICATION_ACCEPT = 1;
    const TYPE_ID_PRODUCT_VERIFICATION_REJECT = 2;
    const TYPE_ID_PARTNERSHIP_OFFER = 3;
    const TYPE_ID_PARTNERSHIP_OFFER_ACCEPTED = 4;
    const TYPE_ID_PARTNERSHIP_TERMINATED = 5;
    const TYPE_ID_SALE_SELLER = 6;
    const TYPE_ID_SALE_PARTNER = 7;
    const TYPE_ID_UNSUCCESSFUL_PAYOUT = 8;
    const TYPE_ID_UNSUCCESSFUL_SUPPORT_ANSWER = 9;
    const TYPE_ID_NEW_CART_ITEM_MESSAGE = 10;
    const TYPE_ID_CART_ITEM_REVIEW = 11;
    const TYPE_ID_PRODUCT_BLOCKED = 12;
    const TYPE_ID_PRODUCT_UNBLOCKED = 13;

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $userId;
    /** @Db\TinyIntType() */
    public $typeId;
    /** @Db\BigIntType() */
    public $referenceId;
    /** @Db\JsonType */
    public $data;
    /** @Db\BoolType() */
    public $isRead = false;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create($userId, $typeId, $referenceId, $data): self
    {
        $item = new self();
        $item->userId = $userId;
        $item->typeId = $typeId;
        $item->referenceId = $referenceId;
        $item->data = $data;

        return $item;
    }
}
