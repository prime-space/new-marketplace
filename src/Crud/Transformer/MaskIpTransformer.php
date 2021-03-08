<?php namespace App\Crud\Transformer;

use App\Currency\CurrencyConverter;
use App\Currency\CurrencyManager;
use App\Entity\Ip;
use Ewll\CrudBundle\ReadViewCompiler\Context;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\ViewTransformerInitializerInterface;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\ViewTransformerInterface;
use RuntimeException;

class MaskIpTransformer implements ViewTransformerInterface
{
    public function transform(
        ViewTransformerInitializerInterface $initializer,
        $item,
        array $transformMap,
        Context $context = null
    ) {
        if (!$initializer instanceof MaskIp) {
            throw new RuntimeException("Expected '".MaskIp::class."', got '".get_class($initializer)."'");
        }

        $fieldName = $initializer->getFieldName();
        $field = $item->$fieldName;
        if (null === $field) {
            return null;
        }
        $pattern = '/(?!\d{1,3}\.\d{1,3}\.)\d/';
        $replacement = '*';
        $result = preg_replace($pattern, $replacement, $field);

        return $result;
    }
}
