<?php namespace App\Repository;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\ProductObject;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\Repository;
use RuntimeException;

class ProductObjectRepository extends Repository
{
    public function forceDeleteUnsoldByProduct(Product $product): void
    {
        $this
            ->dbClient
            ->prepare(<<<SQL
DELETE FROM productObject
WHERE
    productId = :productId
    AND cartItemId IS NULL
SQL
            )
            ->execute(['productId' => $product->id]);
    }

    /** @return CartItem[] */
    public function extractToCartItem(Product $product, CartItem $cartItem): array
    {
        if (Product::TYPE_ID_UNIQUE !== $product->typeId) {
            throw new RuntimeException('Only unique type available here');
        }
        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE productObject
SET cartItemId = :cartItemId
WHERE
    productId = :productId
    AND cartItemId IS NULL
LIMIT {$cartItem->amount}
SQL
            )
            ->execute(['productId' => $product->id, 'cartItemId' => $cartItem->id]);

        $productObjects = $this->findBy(['cartItemId' => $cartItem->id]);

        return $productObjects;
    }

    public function cloneUniversalForCartItem(Product $product, CartItem $cartItem): ProductObject
    {
        if (Product::TYPE_ID_UNIVERSAL !== $product->typeId) {
            throw new RuntimeException('Only universal type available here');
        }
        /** @var ProductObject $originalObject */
        $originalObject = $this->findOneBy(
            ['productId' => $product->id, new FilterExpression(FilterExpression::ACTION_IS_NULL, 'cartItemId')]
        );
        $clonedObject = ProductObject::create($originalObject->productId, $cartItem->id, $originalObject->data);
        $this->create($clonedObject);

        return $clonedObject;
    }
}
