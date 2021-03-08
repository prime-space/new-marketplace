<?php namespace App\Crud\Unit\SellerReview;

use App\Cart\CartManager;
use App\Entity\Product;
use App\Entity\Review;
use App\Factory\EventFactory;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\ReadViewCompiler;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Token\TokenProvider;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SellerReviewCrudUnit extends UnitAbstract implements
    ReadMethodInterface,
    UpdateMethodInterface
{
    const NAME = 'sellerReview';

    private $cartManager;
    private $requestStack;
    private $tokenProvider;
    private $eventFactory;
    private $readViewCompiler;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        CartManager $cartManager,
        RequestStack $requestStack,
        TokenProvider $tokenProvider,
        EventFactory $eventFactory,
        ReadViewCompiler $readViewCompiler
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->cartManager = $cartManager;
        $this->requestStack = $requestStack;
        $this->tokenProvider = $tokenProvider;
        $this->eventFactory = $eventFactory;
        $this->readViewCompiler = $readViewCompiler;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Review::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        $user = $this->getUser();

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
                    [
                        'field' => 'userId',
                        'type' => 'value',
                        'action' => RelationCondition::ACTION_EQUAL,
                        'value' => $user->id
                    ]
                ]
            ),
        ];
    }

    public function getReadListFields(): array
    {
        return Review::getViewTransformerFields();
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig();
        $config
            ->addField('cartItemId', FormType\IntegerType::class);

        return $config;
    }

    public function getUpdateFormConfig(object $entity): FormConfig
    {
        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [
//                new CartPaidNotLongerThan(), //@TODO сделать срок ответа
            ],
        ]);

        $config
            ->addField(Review::FIELD_ANSWER, FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 500]),
                    new Assert\Callback(function ($object, ExecutionContextInterface $context) use ($entity) {
                        /** @var Review $entity */
                        if (null !== $entity->answerTs) {//@TODO хрупко
                            $context->buildViolation('review.already-answered')
                                ->addViolation();
                        }
                    }),
                ],
            ]);

        return $config;
    }

    public function getMutationsOnUpdate(object $entity): array
    {
        return [
            Review::FIELD_ANSWER_TS => new \DateTime(),
        ];
    }

    /** @param $entity Review */
    public function getUpdateExtraData(object $entity): array
    {
        return ['review' => $this->readViewCompiler->compile($entity, Review::getViewTransformerFields())];
    }
}
