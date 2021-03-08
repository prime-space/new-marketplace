<?php namespace App\Crud\Unit\CustomerCartItemReview\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class CartItemNoHaveReview extends Constraint
{
    public $message = 'cart.review.already-reviewed';
}
