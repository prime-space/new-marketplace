<?php namespace App\Crud\Unit\PartnershipAgentProductGroup;

use App\Entity\Product_ProductGroup;
use App\Entity\ProductGroup;
use App\Repository\Product_ProductGroupRepository;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Unit\CreateMethodInterface;
use Ewll\CrudBundle\Unit\DeleteMethodInterface;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class PartnershipAgentProductGroupCrudUnit extends UnitAbstract implements
    ReadMethodInterface,
    CreateMethodInterface,
    UpdateMethodInterface,
    DeleteMethodInterface
{
    const NAME = 'partnershipAgentProductGroup';
    private $domain;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        string $domain
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->domain = $domain;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return ProductGroup::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        return [
            new ExpressionCondition(
                ExpressionCondition::ACTION_EQUAL,
                ProductGroup::FIELD_USER_ID,
                $this->getUser()->id
            ),
        ];
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'name',
            'productsNum',
        ];
    }
    public function getCreateFormConfig(): FormConfig
    {

        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
        ]);
        $config
            ->addField('name', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 1, 'max' => 128]),
                ],
                'label' => 'Название',
            ]);

        return $config;
    }

    /** @param $entity ProductGroup */
    public function getMutationsOnCreate(object $entity): array
    {
        return [ProductGroup::FIELD_USER_ID => $this->getUser()->id];
    }

    public function getUpdateFormConfig(object $entity): FormConfig
    {
        return $this->getCreateFormConfig();
    }

    /** @param $entity ProductGroup */
    public function onDelete(object $entity): void
    {
        /** @var Product_ProductGroupRepository $product_productGroupRepository */
        $product_productGroupRepository = $this->repositoryProvider->get(Product_ProductGroup::class);
        $product_productGroupRepository->forceDeleteByProductGroup($entity);
    }
}
