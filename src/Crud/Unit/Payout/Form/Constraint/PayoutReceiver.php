<?php namespace App\Crud\Unit\Payout\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class PayoutReceiver extends Constraint
{
    const MESSAGE_KEY_INCORRECT = 'receiver.incorrect';
//    const MESSAGE_KEY_SELF = 'constraint.receiver.self';
//    const MESSAGE_KEY_CURRENCY = 'constraint.receiver.currency';
//    const MESSAGE_KEY_NOT_FOUND = 'constraint.receiver.not-found';
}
