<?php namespace App\Crud\Unit\SiteProduct;

use App\Crud\Filter;
use App\Crud\Transformer\ConvertMoney;
use App\Entity\Product;
use App\Entity\StorageFile;
use App\Entity\User;
use App\Sphinx\SphinxClient;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\Extension\Core\Type\SearchType;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SiteProduct extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'siteProduct';
    private $domainBuy;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        string $domainBuy
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->domainBuy = $domainBuy;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Product::class;
    }

    public function getAllowedSortFields(): array
    {
        return [Product::FIELD_SALES_NUM];
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $productStatusesIds = [Product::STATUS_ID_OK, Product::STATUS_ID_OUT_OF_STOCK];

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'statusId', $productStatusesIds),
        ];
    }

    public function getReadOneFields(): array
    {
        $fields = array_merge($this->getReadListFields(), [
            'description',
            'isInStock' => function(Product $product) {
                return $product->statusId === Product::STATUS_ID_OK;
            },
            'url' => function (Product $product) {
                return $product->compileBuyingUrl($this->domainBuy);
            },
        ]);

        return $fields;
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig([
            'data_class' => Filter::class,
        ]);
        $config
            ->addField(
                'query',
                SearchType::class,
                ['entity' => SphinxClient::ENTITY_PRODUCT,]
//            )
//            ->addField(
//                'groupId',
//                FormType\ChoiceType::class,
//                [
//                    'choice_loader' => $this->getProductGroupIdChoiceLoader(),
//                    'property_path' => Product::FIELD_GROUP_IDS,
//                    'label' => 'Группа',
//                ],
            )
            ->addField('productCategoryId', IntegerType::class);;

        return $config;
    }

    public function getReadListFields(): array
    {
        $currencyPlaceholder = sprintf('currency.%s.short-latin', ReadTransformer\Translate::PLACEHOLDER);

        return [
            'id',
            'sellerName' => new ReadTransformer\Entity('userId', User::class, 'getName'),
            'name',
            'salesNum',
            Product::FIELD_REVIEWS_PERCENT,
            'priceView' => [new ConvertMoney('price'),],
            'currencyView' => [new ReadTransformer\Translate('currencyId', 'payment', $currencyPlaceholder)],
            'image' => [new ReadTransformer\Entity('imageStorageFileId', StorageFile::class, 'compilePath')],
            'background' => [new ReadTransformer\Entity('backgroundStorageFileId', StorageFile::class, 'compilePath')],
        ];
    }

//    private function getProductGroupIdChoiceLoader(): EntityChoiceLoader
//    {
//        $choiceLoader = new EntityChoiceLoader(
//            $this->repositoryProvider->get(ProductGroup::class),
//            function (ProductGroup $productGroup) {
//                return $productGroup->name;
//            },
//            [new FilterExpression(FilterExpression::ACTION_EQUAL, 'userId', $this->getUser()->id)],
//            [ProductGroup::FIELD_ID, ProductGroup::FIELD_NAME]
//        );
//
//        return $choiceLoader;
//    }
}
