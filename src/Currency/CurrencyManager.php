<?php namespace App\Currency;

use App\Entity\Currency;
use App\EventSubscriber\LocaleSubscriber;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrencyManager
{
    private $requestStack;
    private $translator;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function getCurrencies(): array
    {
        $currencyIds = [Currency::ID_USD, Currency::ID_EUR, Currency::ID_RUB, Currency::ID_UAH];
        $currencies = [];
        foreach ($currencyIds as $currencyId) {
            $currencies[$currencyId] = [
                'sign' => $this->translator->trans("currency.{$currencyId}.sign", [], 'payment'),
                'short' => $this->translator->trans("currency.{$currencyId}.short-latin", [], 'payment'),
            ];
        }

        return $currencies;
    }

    public function getRequestCurrencyId(): int
    {
        $currencyId = $this->requestStack->getCurrentRequest()->attributes
            ->getInt(LocaleSubscriber::REQUEST_ATTRIBUTE_CURRENCY);
        if (0 === $currencyId) {
            throw new \RuntimeException('Current currency isn`t recognized');
        }

        return $currencyId;
    }
}
