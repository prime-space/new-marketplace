<?php namespace App\EventSubscriber;

use App\Entity\Country;
use App\Sxgeo\Sxgeo;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    const COOKIE_NAME_LOCALE = 'locale';
    const COOKIE_NAME_CURRENCY = 'currency';
    const COOKIE_LIFETIME = 15552000; //half year in seconds
    const LOCALES = [
        1 => 'en',
        2 => 'ru',
    ];
    const REQUEST_ATTRIBUTE_CURRENCY = 'currency';

    private $localeDetectDomains;
    private $defaultLocale;
    private $domain;
    private $repositoryProvider;

    public function __construct(
        array $localeDetectDomains,
        string $defaultLocale,
        string $domain,
        RepositoryProvider $repositoryProvider
    ) {
        $this->localeDetectDomains = $localeDetectDomains;
        $this->defaultLocale = $defaultLocale;
        $this->domain = $domain;
        $this->repositoryProvider = $repositoryProvider;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $country = null;
        $locale = null;
        $currency = null;

        $localeCookie = $request->cookies->get(self::COOKIE_NAME_LOCALE);
        $currencyCookie = $request->cookies->getInt(self::COOKIE_NAME_CURRENCY);

        $isLocaleCookieCorrect = false;
        $isCurrencyCookieCorrect = false;

        if (in_array($localeCookie, array_values(self::LOCALES), true)) {
            $locale = $localeCookie;
            $isLocaleCookieCorrect = true;
        }
        if (in_array($currencyCookie, [1, 2, 3, 4], true)) {//@TODO
            $currency = $currencyCookie;
            $isCurrencyCookieCorrect = true;
        }

        if (null === $locale) {
            if (in_array($request->server->get('HTTP_HOST'), $this->localeDetectDomains, true)) {
                $country = $this->getCountry($request);
                $locale = self::LOCALES[$country->localeId];
            } else {
                $locale = $this->defaultLocale;
            }
        }

        if (null === $currency) {
            if (in_array($request->server->get('HTTP_HOST'), $this->localeDetectDomains, true)) {
                if (null === $country) {
                    $country = $this->getCountry($request);
                }
                $currency = $country->currencyId;
            } else {
                $currency = 1;
            }
        }

        if (!$isLocaleCookieCorrect) {
            $this->setCookie(self::COOKIE_NAME_LOCALE, $locale);
        }
        if (!$isCurrencyCookieCorrect) {
            $this->setCookie(self::COOKIE_NAME_CURRENCY, $currency);
        }

        $request->setLocale($locale);
        $request->attributes->set(self::REQUEST_ATTRIBUTE_CURRENCY, $currency);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    private function setCookie(string $key, string $value)
    {
        SetCookie($key, $value, time() + self::COOKIE_LIFETIME, '/', $this->domain, true, true);
    }

    private function getCountry(Request $request): Country
    {
        $sxgeo = new Sxgeo();
        $alpha2 = $sxgeo->getCountry($request->getClientIp());//@TODO daemon
        unset($sxgeo);
        $country = $this->repositoryProvider->get(Country::class)->findOneBy(['alpha2' => $alpha2]);
        if (null === $country) {
            //@TODO debug
            $country = $this->repositoryProvider->get(Country::class)->findById(Country::COUNTRY_ID_GB);
        }

        return $country;
    }
}
