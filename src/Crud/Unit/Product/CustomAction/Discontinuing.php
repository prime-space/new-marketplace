<?php namespace App\Crud\Unit\Product\CustomAction;

use App\Crud\Unit\Product\ProductCrudUnit;
use App\Entity\Product;
use Ewll\CrudBundle\Exception\ValidationException;
use Ewll\CrudBundle\Unit\CustomActionTargetInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;

class Discontinuing implements CustomActionTargetInterface
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function getName(): string
    {
        return 'discontinuing';
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
        if (!in_array($entity->statusId, [Product::STATUS_ID_OK, Product::STATUS_ID_OUT_OF_STOCK], true)) {
            throw new ValidationException(['Действие невозможно']);
        }
        $entity->statusId = Product::STATUS_ID_DISCONTINUED;
        $this->repositoryProvider->get(Product::class)->update($entity, ['statusId']);

        return [];
    }
}
