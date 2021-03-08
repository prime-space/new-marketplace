<?php namespace App\Form\Constraint;

use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NicknameIsSetValidator extends ConstraintValidator
{
    private $authenticator;

    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NicknameIsSet) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\NicknameIsSet');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (null === $constraint->user->nickname) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
