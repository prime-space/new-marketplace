<?php namespace App\Form\Constraint;

use App\Repository\TreeRepositoryInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TreeIsEmptyValidator extends ConstraintValidator
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TreeIsEmpty) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\TreeIsEmpty');
        }

        $className = get_class($value);
        /** @var TreeRepositoryInterface $repository */
        $repository = $this->repositoryProvider->get($className);
        $hasSubCategory = $repository->hasSubCategory($value->id);
        if ($hasSubCategory) {
            $this->context->buildViolation($constraint->messages['subcategory'])->addViolation();
        }

        $relatedPropertyName = lcfirst(substr(strrchr($className, "\\"), 1)) . 'Id';
        $hasElements = $this->repositoryProvider->get($constraint->related)
            ->findOneBy([$relatedPropertyName => $value->id]);
        if ($hasElements) {
            $this->context->buildViolation($constraint->messages['elements'])->addViolation();
        }
    }
}
