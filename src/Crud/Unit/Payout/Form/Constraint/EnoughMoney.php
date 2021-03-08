<?php namespace App\Crud\Unit\Payout\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class EnoughMoney extends Constraint
{
    public $message = 'payout.amount.insufficient-funds';
}
