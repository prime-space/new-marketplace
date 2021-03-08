<?php namespace App\Crud\Unit\CustomerCartItemReview;

use App\Crud\Unit\CustomerCartItemReview\Form\Constraint\CartItemNoHaveReview;
use App\Crud\Unit\CustomerCartItemReview\Form\Constraint\CartPaidNotLongerThan;
use App\Customer\CustomerIdByRequestFinder;
use App\Entity\CartItem;
use App\Entity\Event;
use App\Entity\Review;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Product\ProductReviewStatActualizer;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\Constraint\EntityAccess;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\ReadViewCompiler;
use Ewll\CrudBundle\Unit\CreateMethodInterface;
use Ewll\CrudBundle\Unit\DeleteMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerCartItemReviewCrudUnit extends UnitAbstract implements
    CreateMethodInterface,
    UpdateMethodInterface,
    DeleteMethodInterface
{
    const NAME = 'customerCartItemReview';

    private $customerIdByRequestFinder;
    private $eventFactory;
    private $readViewCompiler;
    private $productReviewStatActualizer;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CustomerIdByRequestFinder $customerIdByRequestFinder,
        EventFactory $eventFactory,
        ReadViewCompiler $readViewCompiler,
        ProductReviewStatActualizer $productReviewStatActualizer
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->customerIdByRequestFinder = $customerIdByRequestFinder;
        $this->eventFactory = $eventFactory;
        $this->readViewCompiler = $readViewCompiler;
        $this->productReviewStatActualizer = $productReviewStatActualizer;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Review::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $customerId = $this->customerIdByRequestFinder->find();

        return [
            new RelationCondition(
                RelationCondition::COND_RELATE,
                CartItem::class,
                [
                    'field' => 'id',
                    'type' => 'field',
                    'action' => RelationCondition::ACTION_EQUAL,
                    'value' => 'cartItemId'
                ],
                [
                    [
                        'field' => 'customerId',
                        'type' => 'value',
                        'action' => RelationCondition::ACTION_EQUAL,
                        'value' => $customerId
                    ]
                ]
            ),
        ];
    }

    public function getCreateFormConfig(): FormConfig
    {

        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [
                new CartPaidNotLongerThan(),
                new CartItemNoHaveReview(),
            ],
        ]);
        $config
            ->addField(Review::FIELD_CART_ITEM_ID, FormType\IntegerType::class, [
                'constraints' => $this->getCartItemEntityAccessConstraints(),
            ])
            ->addField(Review::FIELD_TEXT, FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 500]),
                ],
            ])
            ->addField(Review::FIELD_IS_GOOD, FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0, 'max' => 1])
                ],
            ]);

        return $config;
    }

    /** @param $entity Review */
    public function getCreateExtraData(object $entity): array
    {
        return ['review' => $this->readViewCompiler->compile($entity, Review::getViewTransformerFields())];
    }

    /** @param $entity Review */
    public function getUpdateExtraData(object $entity): array
    {
        return ['review' => $this->readViewCompiler->compile($entity, Review::getViewTransformerFields())];
    }


    /** @param Review $entity */
    public function getMutationsOnCreate(object $entity): array
    {
        /** @var CartItem $cartItem */
        $cartItem = $this->repositoryProvider->get(CartItem::class)->findById($entity->cartItemId);

        return [Review::FIELD_PRODUCT_ID => $cartItem->productId];
    }

    /** @param Review $entity */
    public function onCreate(object $entity, array $formData): void
    {
        $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
        /** @var CartItem $cartItem */
        $cartItem = $cartItemRepository->findById($entity->cartItemId);

        /** @var User $seller */
        $seller = $this->repositoryProvider->get(User::class)->findById($cartItem->productUserId);

        $this->eventFactory->create(
            $seller->id,
            Event::TYPE_ID_CART_ITEM_REVIEW,
            $cartItem->id,
        );

        $this->productReviewStatActualizer->actualize($entity->productId);
    }

    public function getUpdateFormConfig(object $entity): FormConfig
    {
        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [
                new CartPaidNotLongerThan(),
            ],
        ]);
        $config
            ->addField(Review::FIELD_CART_ITEM_ID, FormType\IntegerType::class, [
                'constraints' => $this->getCartItemEntityAccessConstraints(),
                'disabled' => true,
            ])
            ->addField(Review::FIELD_TEXT, FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 3000]),
                ],
            ])
            ->addField(Review::FIELD_IS_GOOD, FormType\CheckboxType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                ],
                'false_values' => ['0'],
            ]);

        return $config;
    }

    /** @param Review $entity */
    public function onUpdate(object $entity): void
    {
        $this->productReviewStatActualizer->actualize($entity->productId);
    }

    public function getDeleteConstraints(): array
    {
        return [
            'id' => new CartPaidNotLongerThan(),
        ];
    }

    /** @param Review $entity */
    public function onDelete(object $entity): void
    {
        $this->productReviewStatActualizer->actualize($entity->productId);
    }

    private function getCartItemEntityAccessConstraints(): array
    {
        $customerId = $this->customerIdByRequestFinder->find();

        return [
            new Assert\NotBlank(),
            new EntityAccess(CartItem::class, [
                new FilterExpression(FilterExpression::ACTION_EQUAL, CartItem::FIELD_CUSTOMER_ID, $customerId)
            ])
        ];
    }
}
