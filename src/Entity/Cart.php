<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Cart
{
    const STATUS_ID_NEW = 1;
    const STATUS_ID_FIXED = 2;
    const STATUS_ID_PAID = 3;

    const DYNAMICAL_FIELD_CART_ITEMS = 'cartItems';

    const REVIEW_PUBLISHING_EXPIRATION_DAYS = 14;

    const FIELD_CUSTOMER_ID = 'customerId';
    const FIELD_CUSTOMER_IP_ID = 'customerIpId';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $customerId;
    /** @Db\BigIntType() */
    public $customerIpId;
    /** @Db\TinyIntType() */
    public $statusId = self::STATUS_ID_NEW;
    /** @Db\TinyIntType() */
    public $currencyId;
    /** @Db\VarcharType(length = 2) */
    public $locale; //@TODO MUST BE IN Customer
    /** @Db\DecimalType() */
    public $totalProductsAmount;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $paidTs;
    /** @Db\TimestampType */
    public $createdTs;

    //@TODO AbstractEntity
    private $dynamicalProperties = [];

    public function __get($name)
    {
        if (!isset($this->dynamicalProperties[$name])) {
            throw new \RuntimeException("Property '$name' not found");
        }

        if (!array_key_exists('value', $this->dynamicalProperties[$name])) {
            $this->dynamicalProperties[$name]['value'] = call_user_func($this->dynamicalProperties[$name]['method']);
        }

        return $this->dynamicalProperties[$name]['value'];

    }

    public function __set($name, $value)
    {
        if (!isset($this->dynamicalProperties[$name])) {
            throw new \RuntimeException("Property '$name' not found");
        }
        $this->dynamicalProperties[$name]['value'] = $value;
    }

    public function addDynamicalProperty(string $name, callable $method)
    {
        $this->dynamicalProperties[$name] = ['method' => $method];
    }
    //@TODO AbstractEntity

    public static function create(string $customerIpId): self
    {
        $item = new self();
        $item->customerIpId = $customerIpId;

        return $item;
    }

//    public function compileJsData()
//    {
//        return [
//            'id' => $this->id,
//        ];
//    }
    public function isExpiredForReview()
    {
        if (null === $this->paidTs) {
            throw new \LogicException('Cart still not paid');
        }

        $date = new \DateTime(sprintf('-%d days', self::REVIEW_PUBLISHING_EXPIRATION_DAYS));

        return $date > $this->paidTs;
    }
}
