<?php namespace App\Crud\Transformer;

use App\Currency\CurrencyConverter;
use App\Currency\CurrencyManager;
use Ewll\CrudBundle\ReadViewCompiler\Context;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\ViewTransformerInitializerInterface;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\ViewTransformerInterface;
use RuntimeException;

class MaskEmailTransformer implements ViewTransformerInterface
{
    const DOMAIN_ASTERISK_AMOUNT = 5;

    public function transform(
        ViewTransformerInitializerInterface $initializer,
        $item,
        array $transformMap,
        Context $context = null
    ) {
        if (!$initializer instanceof MaskEmail) {
            throw new RuntimeException("Expected '".MaskEmail::class."', got '".get_class($initializer)."'");
        }
        $fieldName = $initializer->getFieldName();
        $field = $item->$fieldName;
        $result = $this->hideEmail($field);

        return $result;
    }

    private function hideEmail($field) {
        [$firstPartOfEmail, $secondPartOfEmail] = explode('@', $field);
        $explodedDomain = explode('.', $secondPartOfEmail);
        $domainCode = implode('.', array_slice($explodedDomain, 1, count($explodedDomain) - 1));
        $lengthOfFirstPartEmail = ceil(strlen($firstPartOfEmail) / 2);
        $result = sprintf(
            '%s%s@%s.%s',
            substr($firstPartOfEmail,0, $lengthOfFirstPartEmail - strlen($firstPartOfEmail) % 2),
            str_repeat('*', $lengthOfFirstPartEmail),
            str_repeat('*', self::DOMAIN_ASTERISK_AMOUNT),
            $domainCode
        );

        return $result;
    }
}
