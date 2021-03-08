<?php namespace App\Crud\Transformer;

use App\Currency\CurrencyConverter;
use App\Currency\CurrencyManager;
use Ewll\CrudBundle\ReadViewCompiler\Context;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\ViewTransformerInitializerInterface;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\ViewTransformerInterface;
use RuntimeException;

class ConvertMoneyTransformer implements ViewTransformerInterface
{
    private $currencyConverter;
    private $currencyManager;

    public function __construct(CurrencyConverter $currencyConverter, CurrencyManager $currencyManager)
    {
        $this->currencyConverter = $currencyConverter;
        $this->currencyManager = $currencyManager;
    }

    public function transform(
        ViewTransformerInitializerInterface $initializer,
        $item,
        array $transformMap,
        Context $context = null
    ) {
        if (!$initializer instanceof ConvertMoney) {
            throw new RuntimeException("Expected '".ConvertMoney::class."', got '".get_class($initializer)."'");
        }

        if (!isset($item->currencyId)) {
            throw new RuntimeException('Item must have currencyId property');
        }

        $fieldName = $initializer->getFieldName();
        $field = $item->$fieldName;

        if (null === $field) {
            return null;
        }

        $toCurrencyId = $this->currencyManager->getRequestCurrencyId();
        $convertedValue = $this->currencyConverter
            ->convert($item->currencyId, $toCurrencyId, $field, 2);//@TODO number of decimals

        $thousandsSeparator = $initializer->isView() ? ',' : '';
        //@TODO number of decimals depend of currency scale
        $result = number_format($convertedValue, 2, '.', $thousandsSeparator);

        return $result;
    }
}
