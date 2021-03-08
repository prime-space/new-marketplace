<?php namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AccuracyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Accuracy) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Accuracy');
        }

        if (null === $value || '' === $value) {
            return;
        }

        $explode = explode('.', $value);
        if (isset($explode[1]) && strlen($explode[1]) > $constraint->accuracy) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%accuracy%', $constraint->accuracy)
                ->addViolation();
        }
    }
}
