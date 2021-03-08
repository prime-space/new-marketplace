<?php namespace App\Currency;

use App\Entity\Currency;
use Ewll\DBBundle\Repository\RepositoryProvider;
use RuntimeException;

class CurrencyConverter
{
    private $repositoryProvider;
    /** @var Currency[] */
    private $currencies = [];

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    /**
     * @throws RuntimeException Unknown currency
     */
    public function convert(int $fromCurrencyId, int $toCurrencyId, string $amount, int $scale = 2): string
    {
        if (0 === count($this->currencies)) {
            $this->currencies = $this->repositoryProvider->get(Currency::class)->findAll('id');
        }

        if (!isset($this->currencies[$fromCurrencyId])) {
            throw new RuntimeException("Unknown 'from' currency #$fromCurrencyId");
        }

        if (!isset($this->currencies[$toCurrencyId])) {
            throw new RuntimeException("Unknown 'to' currency #$toCurrencyId");
        }

        $pointAmount = bcmul($amount, $this->currencies[$fromCurrencyId]->rate, Currency::MAX_SCALE);
        $convertedAmount = bcdiv($pointAmount, $this->currencies[$toCurrencyId]->rate, Currency::MAX_SCALE);

        $convertedAmount = bcmul($convertedAmount, '1', $scale);

        if (1 !== bccomp($convertedAmount, '0', $scale)) {
            $convertedAmount = '0.'.str_pad('1', $scale, '0', STR_PAD_LEFT);
        }

        return $convertedAmount;
    }
}
