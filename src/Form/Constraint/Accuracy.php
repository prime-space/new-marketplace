<?php namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class Accuracy extends Constraint
{
    public $message = 'accuracy';
    public $accuracy;

    public function __construct(int $accuracy)
    {
        parent::__construct(['accuracy' => $accuracy]);
    }

}
