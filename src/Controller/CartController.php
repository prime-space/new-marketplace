<?php namespace App\Controller;

use App\Cart\CartManager;
use App\Cart\Exception\ProductNotAvailableOrNotFoundException;
use App\Currency\CurrencyManager;
use App\Entity\Cart;
use App\Entity\Currency;
use App\Entity\Customer;
use App\EventSubscriber\LocaleSubscriber;
use App\Form\Type\CartItemFixCartType;
use App\Partner\PartnerManager;
use App\Payment\PaymentInitializer;
use App\Product\CartConstraintFactory;
use Ewll\CrudBundle\Constraint\EntityAccess;
use Ewll\CrudBundle\Exception\ValidationException;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Form\FormErrorCompiler;
use Ewll\CrudBundle\Form\FormFactory;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CartController extends AbstractController
{
    const ROUTE_CART = 'cart';

    const FORM_FIX_CART_FIELD_EMAIL = 'email';

    private $siteName;
    private $cdn;
    private $domain;
    private $cartManager;
    private $repositoryProvider;
    private $validator;
    private $partnerManager;
    private $formFactory;
    private $formErrorCompiler;
    private $currencyManager;
    private $paymentInitializer;
    private $translator;

    public function __construct(
        string $siteName,
        string $cdn,
        string $domain,
        CartManager $cartManager,
        RepositoryProvider $repositoryProvider,
        ValidatorInterface $validator,
        PartnerManager $partnerManager,
        FormFactory $formFactory,
        FormErrorCompiler $formErrorCompiler,
        CurrencyManager $currencyManager,
        PaymentInitializer $paymentInitializer,
        TranslatorInterface $translator
    ) {
        $this->siteName = $siteName;
        $this->cdn = $cdn;
        $this->domain = $domain;
        $this->cartManager = $cartManager;
        $this->repositoryProvider = $repositoryProvider;
        $this->validator = $validator;
        $this->partnerManager = $partnerManager;
        $this->formFactory = $formFactory;
        $this->formErrorCompiler = $formErrorCompiler;
        $this->currencyManager = $currencyManager;
        $this->paymentInitializer = $paymentInitializer;
        $this->translator = $translator;
    }

    public function cart(Request $request)
    {
        $jsConfig = [
            'siteName' => $this->siteName,
            'cdn' => $this->cdn,
            'domain' => $this->domain,
            'year' => date('Y'),
            'locales' => LocaleSubscriber::LOCALES,
            'locale' => $request->getLocale(),
            'currencies' => $this->currencyManager->getCurrencies(),
            'currency' => $request->attributes->getInt(LocaleSubscriber::REQUEST_ATTRIBUTE_CURRENCY),
            'subApp' => 'cart',
        ];
        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS)),
            'year' => date('Y'),
            'appName' => 'customer',
            'pageName' => 'page.name.cart',
            'cdn' => $this->cdn,
            'frontDevPort' => '8086',
            'styles' => [
                'https://fonts.googleapis.com/css?family=Rubik:400,500&display=swap',
                'https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css',
            ],
            'scripts' => [
                ['src' => '//code.jivosite.com/widget/p2znDEud0F', 'attributes' => ['async']],
            ],
            'isGoogleAnalytics' => true,
            'isYandexMetrika' => true,
        ];

        return $this->render('app.html.twig', $data);
    }

    public function setLocale(Request $request)
    {
        try {
            $config = new FormConfig();
            $config
                ->addField('locale', FormType\ChoiceType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'choices' => array_combine(LocaleSubscriber::LOCALES, LocaleSubscriber::LOCALES),
                ]);
            $form = $this->formFactory->create($config);
            $form->submit($request->request->get('form'));

            if (!$form->isValid()) {
                $errors = $this->formErrorCompiler->compile($form);

                throw new ValidationException($errors);
            }
            $formData = $form->getData();
            SetCookie(            //@TODO duplicate LocaleSubscriber
                LocaleSubscriber::COOKIE_NAME_LOCALE,
                $formData['locale'],
                time() + LocaleSubscriber::COOKIE_LIFETIME,
                '/',
                $this->domain,
                true,
                true
            );


            return new JsonResponse([]);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function setCurrency(Request $request)
    {
        try {
            $config = new FormConfig();
            $currencyChoices = [];
            foreach ($this->currencyManager->getCurrencies() as $currencyId => $currency) {
                $currencyChoices[$currency['short']] = $currencyId;
            }
            $config
                ->addField('currencyId', FormType\ChoiceType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'choices' => $currencyChoices,
                ]);
            $form = $this->formFactory->create($config);
            $form->submit($request->request->get('form'));

            if (!$form->isValid()) {
                $errors = $this->formErrorCompiler->compile($form);

                throw new ValidationException($errors);
            }
            $formData = $form->getData();
            SetCookie(            //@TODO duplicate LocaleSubscriber
                LocaleSubscriber::COOKIE_NAME_CURRENCY,
                $formData['currencyId'],
                time() + LocaleSubscriber::COOKIE_LIFETIME,
                '/',
                $this->domain,
                true,
                true
            );


            return new JsonResponse([]);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function fix(Request $request)
    {
        try {
            $cart = $this->cartManager->getCart($request->getClientIp());
            $cart->addDynamicalProperty(Cart::DYNAMICAL_FIELD_CART_ITEMS, function () use ($cart): array {
                return [];//@TODO !!!! FLIP PRODUCTS this->repositoryProvider->get(CartItem::class)->findBy(['cartId' => $cart->id]);
            });
            $config = new FormConfig([
                'data_class' => Cart::class,
            ]);
            $config
                ->addField(self::FORM_FIX_CART_FIELD_EMAIL, FormType\EmailType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email(),
                    ],
                    'mapped' => false,
                ])
                ->addField(Cart::DYNAMICAL_FIELD_CART_ITEMS, FormType\CollectionType::class, [
                    'entry_type' => CartItemFixCartType::class,
                    'entry_options' => ['cartId' => $cart->id],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'constraints' => [
                        new Assert\Count(['min' => 1, 'max' => 10, 'minMessage' => 'cart.items.min']),
                    ]
                ]);
            $form = $this->formFactory->create($config, $cart);
            $form->submit($request->request->get('form'));

            if (!$form->isValid()) {
                $errors = $this->formErrorCompiler->compile($form);

                throw new ValidationException($errors);
            }
            /** @var Cart $cart */
            $cart = $form->getData();
            $email = $form->get('email')->getData();
            $orderOnlyToken = $this->cartManager
                ->fixCart($cart, $email, $request->getClientIp(), $request->getLocale());
            if ($cart->currencyId !== Currency::ID_RUB) {
                throw new \LogicException('Only rub available');
            }

            /** @var Customer $customer */
            $customer = $this->repositoryProvider->get(Customer::class)->findById($cart->customerId);
            $formData = $this->paymentInitializer->init($customer, $cart, $orderOnlyToken);

            return new JsonResponse($formData->toArray());
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function setProductAmount(Request $request)
    {
        try {
            $cart = $this->cartManager->getCart($request->getClientIp());
            $config = new FormConfig();
            $config
                ->addField('productId', FormType\IntegerType::class, [
                    'constraints' => CartConstraintFactory::createForProductId($cart->id),
                ])
                ->addField('amount', FormType\IntegerType::class, [
                    'constraints' => CartConstraintFactory::createForAmount(),
                ]);
            $form = $this->formFactory->create($config);
            $form->submit($request->request->get('form'));

            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $cause = $error->getCause();
                    if ($cause instanceof ConstraintViolation) {
                        $constraint = $cause->getConstraint();
                        if ($constraint instanceof EntityAccess) {
                            $messageTemplate = $cause->getMessageTemplate();
                            $messages = $constraint->messages;
                            if ($messageTemplate === $messages[EntityAccess::MESSAGE_KEY_NOT_EXISTS]) {
                                $this->cartManager->setAmount($cart, $cause->getInvalidValue(), 0);
                                throw new ProductNotAvailableOrNotFoundException();
                            }
                        }
                    }
                }
                $errors = $this->formErrorCompiler->compile($form);

                throw new ValidationException($errors);
            }
            $formData = $form->getData();
            $productId = $formData['productId'];
            $amount = $formData['amount'];

            $this->cartManager->setAmount($cart, $productId, $amount);

            return new JsonResponse([]);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        } catch (ProductNotAvailableOrNotFoundException $e) {
            $error = $this->translator->trans("cart.product-not-available-or-not-found", [], 'validators');
            return new JsonResponse([$error], Response::HTTP_GONE);
        }
    }

    public function add(Request $request, int $productId, int $partnerUserId = null)
    {
        $cart = $this->cartManager->getCart($request->getClientIp());
        $errors = $this->validator->validate($productId, CartConstraintFactory::createForProductId($cart->id));
        if (0 === count($errors)) {
            $cart = $this->cartManager->getCart($request->getClientIp());
            $this->cartManager->add($cart, $productId);
        }

        if (null !== $partnerUserId) {
            $this->partnerManager->setPartner($partnerUserId);
        }

        return new JsonResponse([]);
//        return $this->redirectToRoute(self::ROUTE_CART);
    }
}
