<?php namespace App\Crud\Unit\PartnershipAgentProduct_ProductGroup;

use App\Entity\Product;
use App\Entity\Product_ProductGroup;
use App\Entity\ProductGroup;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Unit\CreateMethodInterface;
use Ewll\CrudBundle\Unit\DeleteMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\Repository;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class PartnershipAgentProduct_ProductGroupCrudUnit extends UnitAbstract implements
    CreateMethodInterface,
    DeleteMethodInterface
{
    const NAME = 'partnershipAgentProduct_ProductGroup';

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator
    ) {
        parent::__construct($repositoryProvider, $authenticator);
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Product_ProductGroup::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $productStatusesIds = [Product::STATUS_ID_OK, Product::STATUS_ID_OUT_OF_STOCK];
        return [
            new RelationCondition(
                RelationCondition::COND_RELATE,
                Product::class,
                [
                    'field' => 'id',
                    'type' => 'field',
                    'action' => RelationCondition::ACTION_EQUAL,
                    'value' => 'productId'
                ],
                [
//                    [@TODO DbSource
//                        'field' => 'statusId',
//                        'type' => 'value',
//                        'action' => RelationCondition::ACTION_IN,
//                        'value' => $productStatusesIds
//                    ],
                    [
                        'field' => 'userId',
                        'type' => 'value',
                        'action' => RelationCondition::ACTION_NOT_EQUAL,
                        'value' => $this->getUser()->id
                    ]
                ]
            ),
            new RelationCondition(
                RelationCondition::COND_RELATE,
                ProductGroup::class,
                [
                    'field' => 'id',
                    'type' => 'field',
                    'action' => RelationCondition::ACTION_EQUAL,
                    'value' => 'productGroupId'
                ],
                [

                    [
                        'field' => 'userId',
                        'type' => 'value',
                        'action' => RelationCondition::ACTION_EQUAL,
                        'value' => $this->getUser()->id
                    ]
                ]
            ),
        ];
    }

    public function getCreateFormConfig(): FormConfig
    {

        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
        ]);
        $config
            ->addField('productId', FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->addField('productGroupId', FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);

        return $config;
    }

    /** @param Product_ProductGroup $entity */
    public function onCreate(object $entity, array $formData): void
    {
        $productGroupRepository = $this->repositoryProvider->get(ProductGroup::class);
        /** @var ProductGroup $productGroup */
        $productGroup = $productGroupRepository->findById($entity->productGroupId, Repository::FOR_UPDATE);
        $productGroup->productsNum++;
        $productGroupRepository->update($productGroup, ['productsNum']);
    }

    public function isForceDelete(): bool
    {
        return true;
    }

    /** @param Product_ProductGroup $entity */
    public function onDelete(object $entity): void
    {
        $productGroupRepository = $this->repositoryProvider->get(ProductGroup::class);
        /** @var ProductGroup $productGroup */
        $productGroup = $productGroupRepository->findById($entity->productGroupId, Repository::FOR_UPDATE);
        $productGroup->productsNum--;
        $productGroupRepository->update($productGroup, ['productsNum']);
    }
}
