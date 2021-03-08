<?php namespace App\Crud\Unit\PartnershipAgentProduct;

use App\Crud\Filter;
use App\Entity\Product;
use App\Entity\Product_ProductGroup;
use App\Entity\ProductGroup;
use App\Entity\User;
use App\Sphinx\SphinxClient;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\Extension\Core\Type\SearchType;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Form\ChoiceList\Loader\EntityChoiceLoader;
use Ewll\DBBundle\Query\QueryBuilder;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use Symfony\Component\Form\Extension\Core\Type as FormType;

class PartnershipAgentProductCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'partnershipAgentProduct';
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

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    public function getAllowedSortFields(): array
    {
        return [Product::FIELD_SALES_NUM];
    }


    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new \LogicException('User must be here');
        }
        $productStatusesIds = [Product::STATUS_ID_OK, Product::STATUS_ID_OUT_OF_STOCK];

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'statusId', $productStatusesIds),
            new ExpressionCondition(ExpressionCondition::ACTION_NOT_EQUAL, 'userId', $user->id),
        ];
    }


    public function getReadListPreConditions(): array
    {
        return [new ExpressionCondition(ExpressionCondition::ACTION_GREATER, 'partnershipFee', 0)];
    }

    public function getReadOneFields(): array
    {
        return $this->getReadListFields();
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig([
            'data_class' => Filter::class,
        ]);
        $config
            ->addField(
                'name',
                SearchType::class,
                ['entity' => SphinxClient::ENTITY_PRODUCT,]
            )
            ->addField(
                'groupId',
                FormType\ChoiceType::class,
                [
                    'choice_loader' => $this->getProductGroupIdChoiceLoader(),
                    'property_path' => Product::FIELD_GROUP_IDS,
                    'label' => 'Группа',
                ],
            );

        return $config;
    }

    public function getReadListFields(): array
    {
        $user = $this->getUser();
        $currencyPlaceholder = sprintf('currency.%s.sign', ReadTransformer\Translate::PLACEHOLDER);

        return [
            'id',
            'sellerName' => new ReadTransformer\Entity('userId', User::class, 'getName'),
            'name',
            'salesNum',
            'priceView' => [new ReadTransformer\Money('price', true)],
            'currencyView' => [new ReadTransformer\Translate('currencyId', 'payment', $currencyPlaceholder)],
            'partnershipFee',
            'url' => function (Product $product) use ($user) {
                return $product->compileBuyingUrl($this->domainBuy, $user->id);
            },
            'groups' => [
                new ReadTransformer\EntityRelation(
                    'productId',
                    Product_ProductGroup::class,
                    null,
                    function (QueryBuilder $qb) use ($user) {
                        $prefix = 't' . (count($qb->getJoins()) + 2);
                        $relationRepository = $this->repositoryProvider->get(ProductGroup::class);
                        $relationTableName = $relationRepository->getEntityConfig()->tableName;
                        $joinCondition = sprintf(
                            '%s.%s = %s.%s',
                            $qb->getPrefix(),
                            Product_ProductGroup::FIELD_PRODUCT_GROUP_ID,
                            $prefix,
                            ProductGroup::FIELD_ID
                        );
                        $param1 = [$prefix, ProductGroup::FIELD_USER_ID];
                        $qb
                            ->addJoin($relationTableName, $prefix, $joinCondition)
                            ->addCondition(new FilterExpression(FilterExpression::ACTION_EQUAL, $param1, $user->id));
                    },
                ),
                function (array $groupRelations) {
                    /** @var Product_ProductGroup[] $groupRelations */
                    $views = [];
                    foreach ($groupRelations as $groupRelation) {
                        $views[] = [
                            'id' => $groupRelation->id,
                            'groupId' => $groupRelation->productGroupId,
                        ];
                    }

                    return $views;
                },
            ],
        ];
    }

    private function getProductGroupIdChoiceLoader(): EntityChoiceLoader
    {
        $choiceLoader = new EntityChoiceLoader(
            $this->repositoryProvider->get(ProductGroup::class),
            function (ProductGroup $productGroup) {
                return $productGroup->name;
            },
            [new FilterExpression(FilterExpression::ACTION_EQUAL, 'userId', $this->getUser()->id)],
            [ProductGroup::FIELD_ID, ProductGroup::FIELD_NAME]
        );

        return $choiceLoader;
    }
}
