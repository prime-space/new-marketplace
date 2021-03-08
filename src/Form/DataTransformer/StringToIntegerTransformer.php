<?php namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class StringToIntegerTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ('' === $value || null === $value) {
            return null;
        }
        $transformedValue = (int)$value;

        return $transformedValue;
    }

    public function reverseTransform($value)
    {
        return $value;
    }
}
