<?php namespace App\Crud\Unit\CustomerBlockedEntity;

use App\Crud\Transformer\MaskEmail;
use App\Crud\Transformer\MaskIp;
use App\Entity\CustomerBlockedEntity;
use App\Entity\Ip;
use App\Entity\Product;
use App\Form\Constraint\CustomerBlockedEntityExist;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Unit\CreateMethodInterface;
use Ewll\CrudBundle\Unit\DeleteMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;
use App\Crud\Filter;
use App\Entity\Customer;

class CustomerBlockedEntityCrudUnit extends UnitAbstract implements
    CreateMethodInterface,
    DeleteMethodInterface
{
    const NAME = 'customerBlockedEntity';

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator
    )
    {
        parent::__construct($repositoryProvider, $authenticator);
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return CustomerBlockedEntity::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    public function getAccessConditions(string $action): array
    {
        return [
            new ExpressionCondition(
                ExpressionCondition::ACTION_EQUAL,
                'blockedByUserId', $this->getUser()->id
            ),
        ];
    }

    public function getCreateFormConfig(): FormConfig
    {
        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
        ]);
        $config
            ->addField(CustomerBlockedEntity::FIELD_ENTITY_TYPE, FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Choice([
                        'choices' => $this->getEntityTypeIdChoices(),
                        'message' => 'customer-blocked-entity.invalid-entity-type-id',
                    ]),
                ],
            ])
            ->addField('entityId', FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new CustomerBlockedEntityExist(),
                ],
            ]);

        return $config;
    }

    private function getEntityTypeIdChoices()
    {
        return [
            CustomerBlockedEntity::TYPE_ID_EMAIL,
            CustomerBlockedEntity::TYPE_ID_IP,
        ];
    }

    /** @param $entity Product */
    public function getMutationsOnCreate(object $entity): array
    {
        return ['blockedByUserId' => $this->getUser()->id];
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig([
                'data_class' => Filter::class,
            ]
        );
        $config
            ->addField('id', FormType\IntegerType::class);

        return $config;
    }

    public function getReadOneFields(): array
    {
        return [
            'id',
            'customerEmail' => [
                'value' => function(CustomerBlockedEntity $customerBlockedEntity) {
                    if ($customerBlockedEntity->entityTypeId == CustomerBlockedEntity::TYPE_ID_EMAIL) {
                        return [
                            new ReadTransformer\Entity(
                                CustomerBlockedEntity::FIELD_ENTITY_ID,
                                Customer::class,
                                'email'
                            ),
                            new MaskEmail('email')
                        ];
                    } else {
                        return null;
                    }
                }
            ],
            'ip' => [
                'value' => function(CustomerBlockedEntity $customerBlockedEntity) {
                    if ($customerBlockedEntity->entityTypeId == CustomerBlockedEntity::TYPE_ID_IP) {
                        return [
                            new ReadTransformer\Entity(
                                CustomerBlockedEntity::FIELD_ENTITY_ID,
                                Ip::class
                            ),
                            new MaskIp('ip'),
                        ];
                    } else {
                        return null;
                    }
                }
            ],
            'createdDate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE),
        ];
    }

    public function isForceDelete(): bool
    {
        return true;
    }

    public function getReadListFields(): array
    {
        return $this->getReadOneFields();
    }
}
