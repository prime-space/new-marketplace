<?php namespace App\Form\Constraint;

use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueValidator extends ConstraintValidator
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Unique) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\Unique');
        }

        if (null === $value || '' === $value) {
            return;
        }

        $conditions = array_merge($constraint->getFilters(), [$constraint->getFieldName() => $value]);

        $matches = $this->repositoryProvider->get($constraint->getClassName())->findBy($conditions);
        foreach ($matches as $match) {
            if ($constraint->getExcludeId() !== $match->id) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
