<?php namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class PartnershipNotExistsOrRejected extends Constraint
{
    public $message = 'partnership.active';
}
