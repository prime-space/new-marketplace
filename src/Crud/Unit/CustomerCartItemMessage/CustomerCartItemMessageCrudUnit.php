<?php namespace App\Crud\Unit\CustomerCartItemMessage;

use App\Controller\IndexController;
use App\Customer\CustomerIdByRequestFinder;
use App\Entity\CartItem;
use App\Entity\CartItemMessage;
use App\Entity\Event;
use App\Entity\User;
use App\Factory\EventFactory;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\Constraint\EntityAccess;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\CreateMethodInterface;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MailerBundle\Mailer;
use Ewll\MailerBundle\Template;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Twig\Loader\FilesystemLoader;

class CustomerCartItemMessageCrudUnit extends UnitAbstract implements
    ReadMethodInterface,
    CreateMethodInterface
{
    const NAME = 'customerCartItemMessage';

    const LETTER_NEW_MESSAGE_FROM_CUSTOMER = 'letterNewMessageFromCustomer';

    private $eventFactory;
    private $router;
    private $customerIdByRequestFinder;
    private $mailer;
    private $domain;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        EventFactory $eventFactory,
        CustomerIdByRequestFinder $customerIdByRequestFinder,
        RouterInterface $router,
        Mailer $mailer,
        string $domain
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->eventFactory = $eventFactory;
        $this->customerIdByRequestFinder = $customerIdByRequestFinder;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->domain = $domain;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return CartItemMessage::class;
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

    public function getReadListFields(): array
    {
        return [
            CartItemMessage::FIELD_TEXT,
            CartItemMessage::FIELD_IS_ANSWER,
            'createdDate' => new ReadTransformer\Date(
                CartItemMessage::FIELD_CREATED_TS,
                ReadTransformer\Date::FORMAT_SHORT_DATE_TIME
            ),
        ];
    }

    public function getReadListExtraData(array $context): array
    {
        $cartItemId = $this->findCartItemIdFromConditions($context['conditions']);
        $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
        /** @var CartItem $cartItem */
        $cartItem = $cartItemRepository->findById($cartItemId);
        if ($cartItem->hasUnreadMessagesByCustomer) {
            $cartItem->hasUnreadMessagesByCustomer = false;
            $cartItemRepository->update($cartItem, [CartItem::FIELD_HAS_UNREAD_MESSAGES_BY_CUSTOMER]);
        }

        return [];
    }

    public function getFiltersFormConfig(): ?FormConfig
    {
        $config = new FormConfig();
        $config
            ->addField('cartItemId', FormType\IntegerType::class, [
                'constraints' => $this->getCartItemEntityAccessConstraints(),
            ]);

        return $config;
    }

    public function getCreateFormConfig(): FormConfig
    {

        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
        ]);
        $config
            ->addField('cartItemId', FormType\IntegerType::class, [
                'constraints' => $this->getCartItemEntityAccessConstraints(),
            ])
            ->addField('text', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 3000]),
                ],
            ]);

        return $config;
    }

    /** @param CartItemMessage $entity */
    public function onCreate(object $entity, array $formData): void
    {
        $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
        /** @var CartItem $cartItem */
        $cartItem = $cartItemRepository->findById($entity->cartItemId);

        if (!$cartItem->hasUnreadMessagesBySeller && !$cartItem->isSellerNotificationsDisabled) {
            /** @var User $seller */
            $seller = $this->repositoryProvider->get(User::class)->findById($cartItem->productUserId);

            $this->eventFactory->create(
                $seller->id,
                Event::TYPE_ID_NEW_CART_ITEM_MESSAGE,
                $cartItem->id,
            );

            $orderLink = sprintf('https:%s#messaging', $this->router->generate(
                IndexController::ROUTE_PRIVATE_ORDER,
                ['orderId' => $cartItem->id],
                UrlGeneratorInterface::NETWORK_PATH
            ));
            $letterData = [
                'domain' => $this->domain,
                'orderLink' => $orderLink,
            ];
            $template = new Template(
                self::LETTER_NEW_MESSAGE_FROM_CUSTOMER,
                FilesystemLoader::MAIN_NAMESPACE,
                $letterData
            );
            $this->mailer->createForUser($seller, $template);

            $cartItem->hasUnreadMessagesBySeller = true;
            $cartItemRepository->update($cartItem, ['hasUnreadMessagesBySeller']);
        }
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

    private function findCartItemIdFromConditions(array $conditions): ?int
    {
        foreach ($conditions as $condition) {
            if ($condition instanceof ExpressionCondition && $condition->getField() === 'cartItemId') {
                return $condition->getValue();
            }
        }

        return null;
    }
}
