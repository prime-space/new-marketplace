<?php namespace App\Repository;

use App\Entity\ProductCategory;
use Ewll\DBBundle\Repository\Repository;

class ProductCategoryRepository extends Repository implements TreeRepositoryInterface
{
    public function getFlat(): array
    {
        $prefix = 't1';
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
CALL sp_getProductCategoryTree(1)
SQL
            )
            ->execute();

        /** @var ProductCategory[] $productCategories */
        $productCategories = $this->hydrator
            ->hydrateMany($this->config, $prefix, $statement, $this->getFieldTransformationOptions(), 'id');
        $flat = [];
        foreach ($productCategories as $productCategory) {
            $view = $productCategory->getTreeView();
            $flat[] = $view;
        }

        return $flat;
    }

    public function getSubIds(int $categoryId): array
    {
        $prefix = 't1';
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
CALL sp_getProductCategoryTree(:categoryId)
SQL
            )
            ->execute(['categoryId' => $categoryId]);

        /** @var ProductCategory[] $productCategories */
        $productCategories = $this->hydrator
            ->hydrateMany($this->config, $prefix, $statement, $this->getFieldTransformationOptions(), 'id');
        $ids = [];
        foreach ($productCategories as $productCategory) {
            $ids[] = $productCategory->id;
        }

        return $ids;
    }

    public function hasSubCategory(int $id): bool
    {
        $prefix = 't1';
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
CALL sp_getProductCategoryTree(:id)
SQL
            )
            ->execute(['id' => $id]);

        $data = $statement->fetchArrays();

        return count($data) > 1;
    }
}
