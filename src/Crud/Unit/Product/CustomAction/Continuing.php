<?php namespace App\Crud\Unit\Product\CustomAction;

use App\Crud\Unit\Product\ProductCrudUnit;
use App\Entity\Product;
use App\Product\ProductStatusResolver;
use Ewll\CrudBundle\Exception\ValidationException;
use Ewll\CrudBundle\Unit\CustomActionTargetInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;

class Continuing implements CustomActionTargetInterface
{
    private $repositoryProvider;
    private $productStatusResolver;

    public function __construct(RepositoryProvider $repositoryProvider, ProductStatusResolver $productStatusResolver)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->productStatusResolver = $productStatusResolver;
    }

    public function getName(): string
    {
        return 'continuing';
    }

    public function getUnitName(): string
    {
        return ProductCrudUnit::NAME;
    }

    /**
     * @inheritDoc
     * @param $entity Product
     */
    public function action($entity, array $data): array
    {
        if ($entity->statusId !== Product::STATUS_ID_DISCONTINUED) {
            throw new ValidationException(['Действие невозможно']);
        }
        $entity->statusId = $this->productStatusResolver->resolve($entity, ProductStatusResolver::ACTION_CONTINUING);
        $this->repositoryProvider->get(Product::class)->update($entity, ['statusId']);

        return [];
    }
}
