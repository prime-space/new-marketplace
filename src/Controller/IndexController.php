<?php namespace App\Controller;

use App\Cart\CartManager;
use App\Currency\CurrencyManager;
use App\Entity\Event;
use App\Entity\ProductCategory;
use App\EventSubscriber\LocaleSubscriber;
use App\Repository\EventRepository;
use App\Repository\ProductCategoryRepository;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\AccessRuleProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use Ewll\UserBundle\Twofa\JsConfigCompiler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    const ROUTE_PRIVATE = 'private';
    const ROUTE_PRIVATE_SUPPORT_TICKET = 'private.support';
    const ROUTE_PRIVATE_ORDER = 'private.order';

    private $repositoryProvider;
    private $authenticator;
    private $accessRuleProvider;
    private $jsConfigCompiler;
    private $siteName;
    private $cdn;
    private $domain;
    private $domainDoc;
    private $domainAuth;
    private $currencyManager;
    private $domainApi;
    private $domainPrivate;
    private $emailInfo;
    private $domainCustomer;
    private $domainBuy;
    private $cartManager;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        AccessRuleProvider $accessRuleProvider,
        JsConfigCompiler $jsConfigCompiler,
        string $siteName,
        string $cdn,
        string $domain,
        string $domainDoc,
        string $domainAuth,
        CurrencyManager $currencyManager,
        string $domainApi,
        string $domainPrivate,
        string $emailInfo,
        string $domainCustomer,
        string $domainBuy,
        CartManager $cartManager
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->authenticator = $authenticator;
        $this->accessRuleProvider = $accessRuleProvider;
        $this->jsConfigCompiler = $jsConfigCompiler;
        $this->siteName = $siteName;
        $this->cdn = $cdn;
        $this->domain = $domain;
        $this->domainDoc = $domainDoc;
        $this->domainAuth = $domainAuth;
        $this->currencyManager = $currencyManager;
        $this->domainApi = $domainApi;
        $this->domainPrivate = $domainPrivate;
        $this->emailInfo = $emailInfo;
        $this->domainCustomer = $domainCustomer;
        $this->domainBuy = $domainBuy;
        $this->cartManager = $cartManager;
    }

    public function index(Request $request)
    {
        /** @var ProductCategoryRepository $productCategoryRepository */
        $productCategoryRepository = $this->repositoryProvider->get(ProductCategory::class);
        $jsConfig = [
            'siteName' => $this->siteName,
            'cdn' => $this->cdn,
            'domain' => $this->domain,
            'domainAuth' => $this->domainAuth,
            'domainCustomer' => $this->domainCustomer,
            'domainBuy' => $this->domainBuy,
            'year' => date('Y'),
            'locales' => LocaleSubscriber::LOCALES,
            'locale' => $request->getLocale(),
            'currencies' => $this->currencyManager->getCurrencies(),
            'currency' => $request->attributes->getInt(LocaleSubscriber::REQUEST_ATTRIBUTE_CURRENCY),
            'emailInfo' => $this->emailInfo,
            'productCategoryTreeFlat' => $productCategoryRepository->getFlat(),
            'cart' => [
                'itemsAmount' => $this->cartManager->getItemsAmount(),
            ],
        ];
        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS)),
            'year' => date('Y'),
            'appName' => 'site',
            'pageName' => null,
            'cdn' => $this->cdn,
            'frontDevPort' => '8087',
            'styles' => [
                'https://fonts.googleapis.com/css?family=Rubik:400,500&display=swap',
//                'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css',
                'https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css',
            ],
            'scripts' => [],
            'isGoogleAnalytics' => true,
            'isYandexMetrika' => true,
        ];

        return $this->render('app.html.twig', $data);
    }

    public function private()
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            return $this->redirect('//' . $this->domainAuth);
        }

        /** @var ProductCategoryRepository $productCategoryRepository */
        $productCategoryRepository = $this->repositoryProvider->get(ProductCategory::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->repositoryProvider->get(Event::class);
        $jsConfig = [
            'token' => $user->token->data['csrf'],
            'accessRulesIndexedById' => $this->accessRuleProvider->compileJsConfigViewsIndexedById(),
            'userAccessRights' => $user->accessRights,
            'siteName' => $this->siteName,
            'user' => $user->compileJsConfigView(),
            'productCategoryTreeFlat' => $productCategoryRepository->getFlat(),
            'cdn' => $this->cdn,
            'haveUnreadEvent' => $eventRepository->haveUnreadEvent($user->id),
            'twofa' => $this->jsConfigCompiler->compile($user),
            'domainDoc' => $this->domainDoc,
            'domainAuth' => $this->domainAuth,
        ];
        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS)),
            'year' => date('Y'),
            'token' => $user->token->data['csrf'],
            'appName' => 'admin',
            'pageName' => 'private',
            'cdn' => $this->cdn,
            'frontDevPort' => '8080',
            'styles' => [
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',
                'https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css',
            ],
            'scripts' => [],
        ];

        return $this->render('app.html.twig', $data);
    }

    public function doc()
    {
        $jsConfig = [
            'siteName' => $this->siteName,
            'cdn' => $this->cdn,
            'domain' => $this->domainDoc,
            'domainPrivate' => $this->domainPrivate,
            'domainApi' => $this->domainApi,
        ];
        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS)),
            'year' => date('Y'),
            'appName' => 'doc',
            'pageName' => 'documentation',
            'cdn' => $this->cdn,
            'frontDevPort' => '8085',
            'styles' => [
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',
                'https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css',
            ],
            'scripts' => [],
            'isGoogleAnalytics' => true,
            'isYandexMetrika' => true,
        ];

        return $this->render('app.html.twig', $data);
    }

    public function landing(string $landingName)
    {
        $data = [
            'siteName' => $this->siteName,
            'landingName' => $landingName,
            'cdn' => $this->cdn,
        ];

        return $this->render("landing/$landingName.html.twig", $data);
    }
}
