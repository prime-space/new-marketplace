<?php namespace App\Product;

use App\Entity\Product;
use RuntimeException;

class ProductStatusResolver
{
    const ACTION_VERIFICATION_ACCEPT = 1;
    const ACTION_CONTINUING = 2;
    const ACTION_OBJECT_MANIPULATING = 3;
    const ACTION_SALE = 4;
    const ACTION_BLOCK = 5;
    const ACTION_VERIFICATION_REJECT = 6;
    const ACTION_VERIFICATION_UNBLOCK = 7;
    const ACTION_OBJECT_ADD = 8;

    public function resolve(Product $product, int $action): int
    {
        switch ($action) {
            case self::ACTION_VERIFICATION_REJECT:
                $status = Product::STATUS_ID_REJECTED;
                break;
            case self::ACTION_BLOCK:
                $status = Product::STATUS_ID_BLOCKED;
                break;
            case self::ACTION_OBJECT_ADD:
            case self::ACTION_OBJECT_MANIPULATING:
                $status = $product->statusId === Product::STATUS_ID_OUT_OF_STOCK
                    ? Product::STATUS_ID_OK
                    : $product->statusId;
                break;
            case self::ACTION_VERIFICATION_ACCEPT:
            case self::ACTION_CONTINUING:
            case self::ACTION_VERIFICATION_UNBLOCK:
            case self::ACTION_SALE:
                $status = $this->isOkOrOutOfStock($product);
                break;
            default:
                throw new RuntimeException('Unknown action');
        }

        return $status;
    }

    private function isOkOrOutOfStock(Product $product)
    {
        return $product->inStockNum > 0 ? Product::STATUS_ID_OK : Product::STATUS_ID_OUT_OF_STOCK;
    }
}
