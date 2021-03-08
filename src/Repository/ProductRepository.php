<?php namespace App\Repository;

use App\Entity\Product;
use Ewll\DBBundle\Repository\Repository;

class ProductRepository extends Repository
{
    public function salesUp(Product $product): void
    {
        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE product
SET salesNum = salesNum + 1
WHERE id = :id
SQL
            )
            ->execute(['id' => $product->id]);
    }

    public function salesDown(Product $product): void
    {
        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE product
SET salesNum = salesNum - 1
WHERE id = :id
SQL
            )
            ->execute(['id' => $product->id]);
    }
}
