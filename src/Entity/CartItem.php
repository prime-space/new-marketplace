<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class CartItem
{
    const FIELD_ID = 'id';
    const FIELD_CUSTOMER_ID = 'customerId';
    const FIELD_CART_ID = 'cartId';
    const FIELD_PRODUCT_USER_ID = 'productUserId';
    const FIELD_HAS_UNREAD_MESSAGES_BY_SELLER = 'hasUnreadMessagesBySeller';
    const FIELD_HAS_UNREAD_MESSAGES_BY_CUSTOMER = 'hasUnreadMessagesByCustomer';
    const FIELD_CUSTOMER = 'customer';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $customerId;
    /** @Db\BigIntType() */
    public $cartId;
    /** @Db\BigIntType() */
    public $productId;
    /** @Db\BigIntType() */
    public $productUserId;
    /** @Db\BigIntType() */
    public $partnerId;
    /** @Db\TinyIntType() */
    public $currencyId;
    /** @Db\JsonType() */
    public $productObjectIds = [];
    /** @Db\JsonType() */
    public $calculations = [];
    /** @Db\IntType() */
    public $amount;
    /** @Db\DecimalType() */
    public $price;
    /** @Db\DecimalType() */
    public $productPrice;
    /** @Db\IntType() */
    public $amountInFact;
    /** @Db\BoolType() */
    public $isPaid = false;
    /** @Db\BoolType() */
    public $hasUnreadMessagesBySeller = false;
    /** @Db\BoolType() */
    public $hasUnreadMessagesByCustomer = false;
    /** @Db\BoolType() */
    public $isSellerNotificationsDisabled = false;
    /** @Db\BoolType() */
    public $isCustomerNotificationsDisabled = false;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    /** @Db\ManyToOne(RelationClassName=Customer::class) */
    public $customer;

    public static function create($cartId, $productId, $productUserId, $amount): self
    {
        $item = new self();
        $item->cartId = $cartId;
        $item->productId = $productId;
        $item->productUserId = $productUserId;
        $item->amount = $amount;

        return $item;
    }
}
