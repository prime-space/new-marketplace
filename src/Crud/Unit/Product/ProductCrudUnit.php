<?php namespace App\Crud\Unit\Product;

use App\Crud\Unit\Product\CustomAction as CustomAction;
use App\Entity\Currency;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\StorageFile;
use App\Form\Constraint\Accuracy;
use App\Form\Constraint\NicknameIsSet;
use App\Form\DataTransformer\StorageFileToBase64ViewTransformerFactory;
use App\Form\DataTransformer\StringToIntegerTransformer;
use App\Sphinx\SphinxClient;
use Ewll\CrudBundle\Constraint\EntityAccess;
use Ewll\CrudBundle\Form\Extension\Core\Type\SearchType;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Unit\CreateMethodInterface;
use Ewll\CrudBundle\Unit\DeleteMethodInterface;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Form\ChoiceList\Loader\EntityChoiceLoader;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductCrudUnit extends UnitAbstract implements
    ReadMethodInterface,
    CreateMethodInterface,
    UpdateMethodInterface,
    DeleteMethodInterface
{
    const NAME = 'product';

    private $storageFileToBase64ViewTransformerFactory;
    private $translator;
    private $sphinxClient;
    private $domain;
    private $domainBuy;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        StorageFileToBase64ViewTransformerFactory $storageFileToBase64ViewTransformerFactory,
        TranslatorInterface $translator,
        SphinxClient $sphinxClient,
        string $domain,
        string $domainBuy
    )
    {
        parent::__construct($repositoryProvider, $authenticator);
        $this->storageFileToBase64ViewTransformerFactory = $storageFileToBase64ViewTransformerFactory;
        $this->translator = $translator;
        $this->sphinxClient = $sphinxClient;
        $this->domain = $domain;
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

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'userId', $this->getUser()->id),
        ];
    }

    public function getReadOneFields(): array
    {
//        $type = sprintf('type.%s', ReadTransformer\Translate::PLACEHOLDER);
        $statusPlaceholder = sprintf('status.%s', ReadTransformer\Translate::PLACEHOLDER);
        $currencyPlaceholder = sprintf('currency.%s.sign', ReadTransformer\Translate::PLACEHOLDER);
        return [
            'id',
            'type' => [new ReadTransformer\Translate('typeId', 'product', $statusPlaceholder)],
            'statusId',
            'status' => [new ReadTransformer\Translate('statusId', 'product', $statusPlaceholder)],
            'image' => [new ReadTransformer\Entity('imageStorageFileId', StorageFile::class, 'compilePath')],
            'currencyId',
            'currency' => [new ReadTransformer\Translate('currencyId', 'payment', $currencyPlaceholder)],
            'productCategoryId',
            'productCategoryName' => [new ReadTransformer\Entity('productCategoryId', ProductCategory::class, 'name')],
            'name',
            'priceView' => [new ReadTransformer\Money('price', true)],
            'price' => [new ReadTransformer\Money('price')],
            'salesNum',
            'inStockNum' => function (Product $product) {
                if (0 === $product->inStockNum) {
                    return 0;
                } elseif ($product->typeId === Product::TYPE_ID_UNIVERSAL) {
                    return '∞';
                } elseif ($product->typeId === Product::TYPE_ID_UNIQUE) {
                    return $product->inStockNum;
                } else {
                    throw new RuntimeException('Unknown product type');
                }
            },
            'urlPage' => function (Product $product) {
                return $product->compilePageUrl($this->domain);
            },
            'urlAddToCart' => function (Product $product) {
                return $product->compileBuyingUrl($this->domainBuy);
            },
        ];
    }

    public function getReadListFields(): array
    {
        return $this->getReadOneFields();
    }

    public function getPreSort(): array
    {
        return [
//            [
//                'type' => Repository::SORT_TYPE_SIMPLE,
//                'method' => 'desc',
//                'field' => 'isDraft'
//            ],
        ];
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig();
        $config
            ->addField('name', SearchType::class, ['entity' => SphinxClient::ENTITY_PRODUCT]);

        return $config;
    }

    public function getCreateFormConfig(): FormConfig
    {

        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [new NicknameIsSet($this->getUser())],
        ]);
        $config
            ->addField('typeId', FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => ' ']),//hack: space for vuetify error
                ],
                'choices' => $this->getTypeChoices(),
            ]);

        return $config;
    }

    public function getUpdateFormConfig(object $entity): FormConfig
    {
        $translator = $this->translator;
        $currencyEntityChoiceLoader = new EntityChoiceLoader(
            $this->repositoryProvider->get(Currency::class),
            function (Currency $currency) use ($translator) {
                return $translator->trans("currency.$currency->id.name", [], 'payment');
            }
        );
        $config = new FormConfig(['data_class' => $this->getEntityClass()]);
        $config
            ->addField('statusId', FormType\IntegerType::class, [
                'disabled' => true,
            ], new StringToIntegerTransformer())
            ->addField('typeId', FormType\ChoiceType::class, [
                'disabled' => true,
                'choices' => $this->getTypeChoices(),
            ])
            ->addField('verificationRejectReason', FormType\TextType::class, [
                'disabled' => true,
            ])
            ->addField('imageStorageFileId', FormType\TextType::class, [//@TODO preformation for "image"
                'constraints' => [
                    new Assert\NotBlank(['message' => 'image.not-choosed']),
                ],
            ], $this->storageFileToBase64ViewTransformerFactory->create(300, 160, 200000))
            ->addField('backgroundStorageFileId', FormType\TextType::class, [//@TODO preformation for "image"
//                'constraints' => [
//                    new Assert\NotBlank(['message' => 'image.not-choosed']),
//                ],
            ], $this->storageFileToBase64ViewTransformerFactory->create(1110, 170, 500000))
            ->addField('currencyId', FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'choice_loader' => $currencyEntityChoiceLoader,
            ])
            ->addField('productCategoryId', FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'tree.not-choosed']),
                    new EntityAccess(ProductCategory::class),
                ],
            ], new StringToIntegerTransformer())
            ->addField('name', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 128]),
                ],
            ])
            ->addField('price', FormType\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank,
                    new Assert\GreaterThan(0),
                    new Assert\LessThanOrEqual(1000000),
                    new Accuracy(2),
                ],
            ])
            ->addField('partnershipFee', FormType\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0, 'max' => 90]),
                    new Accuracy(2),
                ],
            ])
            ->addField('description', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 10000]),
                ],
            ]);

        return $config;
    }

    /** @param $entity Product */
    public function getMutationsOnCreate(object $entity): array
    {
        return ['userId' => $this->getUser()->id];
    }

    /** @param $entity Product */
    public function getMutationsOnUpdate(object $entity): array
    {
        $mutations = [];
        if (Product::STATUS_ID_FETUS === $entity->statusId) {
            $mutations['statusId'] = Product::STATUS_ID_DRAFT;
        } elseif (in_array($entity->statusId, Product::STATUSES_CHECK_AGAIN_AFTER_EDITION, true)) {
            $mutations['statusId'] = Product::STATUS_ID_VERIFICATION;
        }

        return $mutations;
    }

    /** @param $entity Product */
    public function onUpdate(object $entity): void
    {
        $this->sphinxClient->put(SphinxClient::ENTITY_PRODUCT, $entity->id, ['name' => $entity->name]);
    }

    public function getReadListPreConditions(): array
    {
        return [new ExpressionCondition(ExpressionCondition::ACTION_NOT_EQUAL, 'statusId', Product::STATUS_ID_FETUS)];
    }

    public function getCustomActions(): array
    {
        return [
            CustomAction\SendToVerification::class,
            CustomAction\Continuing::class,
            CustomAction\Discontinuing::class,
            CustomAction\ObjectManipulating::class,
            CustomAction\ObjectsAdd::class,
        ];
    }

    private function getTypeChoices(): array
    {
        $types = [];
        foreach (Product::TYPES as $typeId) {
            $key = $this->translator->trans("type.$typeId", [], 'product');
            $types[$key] = $typeId;
        }
        return $types;
    }
}
