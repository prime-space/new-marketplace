<?php namespace App\Controller;

use App\Currency\CurrencyManager;
use App\Customer\Token\CustomerToken;
use App\Entity\Customer;
use App\EventSubscriber\LocaleSubscriber;
use App\Form\DataTransformer\CustomerToEmailTransformer;
use App\Payment\PaymentInitializer;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MailerBundle\Mailer;
use Ewll\MailerBundle\Template;
use Ewll\UserBundle\Entity\Token;
use Ewll\UserBundle\Form\Constraints\Captcha;
use Ewll\UserBundle\Form\FormErrorResponse;
use Ewll\UserBundle\Token\Exception\TokenNotFoundException;
use Ewll\UserBundle\Token\TokenProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type as FieldType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints;
use Twig\Loader\FilesystemLoader;

class CustomerController extends AbstractController
{
    const ROUTE_CUSTOMER = 'customer';
    const ROUTE_CUSTOMER_SPACE_AUTH = 'customer.space.auth';
    const ROUTE_CUSTOMER_ORDER_ITEM = 'customer.order.item';

    const QUERY_PARAM_TOKEN_NOT_FOUND = 'tokenNotFound';

    const LETTER_NAME_CUSTOMER_SPACE_ACCESS = 'letterCustomerSpaceAccess';

    const COOKIE_NAME = 'cs';

    private $siteName;
    private $cdn;
    private $domain;
    private $currencyManager;
    private $tokenProvider;
    private $repositoryProvider;
    private $paymentInitializer;
    private $mailer;
    private $router;
    private $customerToEmailTransformer;

    public function __construct(
        string $siteName,
        string $cdn,
        string $domain,
        CurrencyManager $currencyManager,
        TokenProvider $tokenProvider,
        RepositoryProvider $repositoryProvider,
        PaymentInitializer $paymentInitializer,
        Mailer $mailer,
        RouterInterface $router,
        CustomerToEmailTransformer $customerToEmailTransformer
    ) {
        $this->siteName = $siteName;
        $this->cdn = $cdn;
        $this->domain = $domain;
        $this->currencyManager = $currencyManager;
        $this->tokenProvider = $tokenProvider;
        $this->repositoryProvider = $repositoryProvider;
        $this->paymentInitializer = $paymentInitializer;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->customerToEmailTransformer = $customerToEmailTransformer;
    }

    public function auth(Request $request, string $tokenKey)
    {
        try {
            $token = $this->tokenProvider->getByCode($tokenKey, CustomerToken::TYPE_ID);
        } catch (TokenNotFoundException $e) {
            $link = sprintf(
                'https:%s?%s=1',
                $this->router->generate(self::ROUTE_CUSTOMER, [], UrlGeneratorInterface::NETWORK_PATH),
                self::QUERY_PARAM_TOKEN_NOT_FOUND,
            );

            return $this->redirect($link);
        }
        $duration = CustomerToken::LIFE_TIME * 60;
        $this->setCookie($tokenKey, $duration);

        return $this->redirectToRoute(self::ROUTE_CUSTOMER);
    }

    public function page(Request $request)
    {
        $token = $this->findTokenFromCookie($request);
        $isAuthorized = $token !== null;

        $jsConfig = [
            'siteName' => $this->siteName,
            'cdn' => $this->cdn,
            'domain' => $this->domain,
            'year' => date('Y'),
            'locales' => LocaleSubscriber::LOCALES,
            'locale' => $request->getLocale(),
            'currencies' => $this->currencyManager->getCurrencies(),
            'currency' => $request->attributes->getInt(LocaleSubscriber::REQUEST_ATTRIBUTE_CURRENCY),
            'subApp' => $isAuthorized ? 'customer' : 'customer_login',
            'isTokenNotFound' => $request->query->getInt(self::QUERY_PARAM_TOKEN_NOT_FOUND) === 1,
        ];
        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS)),
            'year' => date('Y'),
            'appName' => 'customer',
            'pageName' => 'page.name.customer',
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

    public function login(Request $request)
    {
        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('email', FieldType\TextType::class, [
                'constraints' => [new Constraints\NotBlank()]
            ])
            ->add('captcha', FieldType\IntegerType::class, ['constraints' => [new Captcha(['email'])]]);
        $formBuilder->get('email')->addModelTransformer($this->customerToEmailTransformer);
        $form = $formBuilder->getForm();
        $form->submit($request->request->get('form', []));

        if ($form->isValid()) {
            $data = $form->getData();
            /** @var Customer $customer */
            $customer = $data['email'];
            $tokenData = ['customerId' => $customer->id];
            $token = $this->tokenProvider->generate(CustomerToken::class, $tokenData, $request->getClientIp());
            $customerLink = 'https:' . $this->router->generate(
                    self::ROUTE_CUSTOMER_SPACE_AUTH,
                    ['tokenKey' => $this->tokenProvider->compileTokenCode($token)],
                    UrlGeneratorInterface::NETWORK_PATH
                );
            $templateData = [
                'domain' => $this->domain,
                'customerLink' => $customerLink,
            ];
            $template = new Template(
                self::LETTER_NAME_CUSTOMER_SPACE_ACCESS,
                FilesystemLoader::MAIN_NAMESPACE,
                $templateData
            );
            $this->mailer->create($customer->email, $template);

            return new JsonResponse();
        }

        return new FormErrorResponse($form);
    }

    public function logout(Request $request)
    {
        $token = $this->findTokenFromCookie($request);
        if (null !== $token) {
            $this->tokenProvider->toUse($token);
        }
        $this->setCookie('', -1);

        return $this->redirectToRoute(self::ROUTE_CUSTOMER);
    }

    private function findTokenFromCookie(Request $request): ?Token
    {
        $tokenKey = $request->cookies->get(self::COOKIE_NAME, '');
        try {
            $token = $this->tokenProvider->getByCode($tokenKey, CustomerToken::TYPE_ID);

            return $token;
        } catch (TokenNotFoundException $e) {
            return null;
        }
    }

    private function setCookie(string $value, int $duration)
    {
        SetCookie(self::COOKIE_NAME, $value, time() + $duration, '/', $this->domain, true, true);
    }
}
