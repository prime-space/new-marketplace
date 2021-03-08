<?php namespace App\Crud\Unit\SiteProductReview;

use App\Entity\Review;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Symfony\Component\Form\Extension\Core\Type as FormType;

class SiteProductReview extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'siteProductReview';

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Review::class;
    }

    public function getAccessConditions(string $action): array
    {
        return [];
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig();
        $config
            ->addField('productId', FormType\IntegerType::class);

        return $config;
    }

    public function getReadListFields(): array
    {
        return Review::getViewTransformerFields();
    }
}
