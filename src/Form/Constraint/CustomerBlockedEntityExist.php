<?php namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class CustomerBlockedEntityExist extends Constraint
{
    public $message = 'customer-blocked-entity.invalid-entity-id';
}
