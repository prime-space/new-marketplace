<?php namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class CustomerIsNotBlockedBySeller extends Constraint
{
    public $message = 'customer.is-blocked';
    public $cartId;

    public function __construct(int $cartId)
    {
        parent::__construct();
        $this->cartId = $cartId;
    }
}
