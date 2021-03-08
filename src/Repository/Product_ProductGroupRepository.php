<?php namespace App\Repository;

use App\Entity\ProductGroup;
use Ewll\DBBundle\Repository\Repository;

class Product_ProductGroupRepository extends Repository
{
    public function forceDeleteByProductGroup(ProductGroup $productGroup): void
    {
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
DELETE from product_productGroup
WHERE
    productGroupId = :productGroupId
SQL
            )
            ->execute(['productGroupId' => $productGroup->id]);
    }
}
