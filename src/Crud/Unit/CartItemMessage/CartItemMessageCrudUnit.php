<?php namespace App\Crud\Unit\CartItemMessage;

use App\Api\Item\Admin\Handler\TicketApiHandler;
use App\Controller\CustomerController;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\CartItemMessage;
use App\Entity\Customer;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\Constraint\EntityAccess;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit as UnitMethod;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MailerBundle\Mailer;
use Ewll\MailerBundle\Template;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Loader\FilesystemLoader;

class CartItemMessageCrudUnit extends UnitAbstract implements
    UnitMethod\ReadMethodInterface,
    UnitMethod\CreateMethodInterface
{
    const NAME = 'cartItemMessage';

    const LETTER_NEW_MESSAGE_FROM_SELLER = 'letterNewMessageFromSeller';

    private $translator;
    private $ticketApiHandler;
    private $messageBroker;
    private $router;
    private $mailer;
    private $domain;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        TranslatorInterface $translator,
        TicketApiHandler $ticketApiHandler,
        MessageBroker $messageBroker,
        RouterInterface $router,
        Mailer $mailer,
        string $domain
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->translator = $translator;
        $this->ticketApiHandler = $ticketApiHandler;
        $this->messageBroker = $messageBroker;
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
                CartItem::class,
                [
                    'field' => 'id',
                    'type' => 'field',
                    'action' => RelationCondition::ACTION_EQUAL,
                    'value' => 'cartItemId'
                ],
                [
                    [
                        'field' => 'productUserId',
                        'type' => 'value',
                        'action' => RelationCondition::ACTION_EQUAL,
                        'value' => $user->id,
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
        if ($cartItem->hasUnreadMessagesBySeller) {
            $cartItem->hasUnreadMessagesBySeller = false;
            $cartItemRepository->update($cartItem, [CartItem::FIELD_HAS_UNREAD_MESSAGES_BY_SELLER]);
        }

        return [
            'isSellerNotificationsDisabled' => $cartItem->isSellerNotificationsDisabled
        ];
    }

    public function getCreateFormConfig(): FormConfig
    {
        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [],
        ]);
        $config
            ->addField('cartItemId', FormType\IntegerType::class, [
                'constraints' => $this->getCartItemEntityAccessConstraints(),
            ])
            ->addField('text', FormType\TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 3000]),
                ],
            ]);

        return $config;
    }

    public function getMutationsOnCreate(object $entity): array
    {
        return [CartItemMessage::FIELD_IS_ANSWER => true];
    }

    /** @param CartItemMessage $entity */
    public function onCreate(object $entity, array $formData): void
    {
        $cartItemRepository = $this->repositoryProvider->get(CartItem::class);
        /** @var CartItem $cartItem */
        $cartItem = $cartItemRepository->findById($entity->cartItemId);
        if (!$cartItem->hasUnreadMessagesByCustomer && !$cartItem->isCustomerNotificationsDisabled) {
            /** @var Customer $customer */
            $customer = $this->repositoryProvider->get(Customer::class)->findById($cartItem->customerId);

            $orderLink = 'https:' . $this->router->generate(
                    CustomerController::ROUTE_CUSTOMER_ORDER_ITEM,
                    ['cartId' => $cartItem->cartId, 'cartItemId' => $cartItem->id],
                    UrlGeneratorInterface::NETWORK_PATH
                );
            $letterData = [
                'domain' => $this->domain,
                'orderLink' => $orderLink,
            ];
            $template = new Template(
                self::LETTER_NEW_MESSAGE_FROM_SELLER,
                FilesystemLoader::MAIN_NAMESPACE,
                $letterData
            );
            $this->mailer->create($customer->email, $template);

            $cartItem->hasUnreadMessagesByCustomer = true;
            $cartItemRepository->update($cartItem, ['hasUnreadMessagesByCustomer']);
        }
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

    private function getCartItemEntityAccessConstraints()
    {
        $user = $this->getUser();

        return [
            new Assert\NotBlank(),
            new EntityAccess(CartItem::class, [
                new FilterExpression(FilterExpression::ACTION_EQUAL, CartItem::FIELD_PRODUCT_USER_ID, $user->id)
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
