<?php namespace App\Crud\Unit\CustomerCartItemReview\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class CartPaidNotLongerThan extends Constraint
{
    public $message = 'cart.review.date-expired';
}
