<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class ProductObject
{
    const FIELD_CART_ITEM_ID = 'cartItemId';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $productId;
    /** @Db\BigIntType() */
    public $cartItemId;
    /** @Db\TextType() */
    public $data;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $reservedTs;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create($productId, $cartItemId, $data): self
    {
        $item = new self();
        $item->productId = $productId;
        $item->cartItemId = $cartItemId;
        $item->data = $data;

        return $item;
    }

    public function compileAdminListView()
    {
        return [
            'id' => $this->id,
            'data' => $this->data,
        ];
    }
}

