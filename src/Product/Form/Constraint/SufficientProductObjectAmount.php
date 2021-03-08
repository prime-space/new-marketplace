<?php namespace App\Product\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class SufficientProductObjectAmount extends Constraint
{
    const MESSAGE_CODE_NOT_ENOUGHT = 1;
    const MESSAGE_CODE_NOT_IN_STOCK = 2;
    public $messages = [
        self::MESSAGE_CODE_NOT_ENOUGHT => 'sufficient-product-object-amount.not-enought',
        self::MESSAGE_CODE_NOT_IN_STOCK => 'sufficient-product-object-amount.not-in-stock',
    ];
}
