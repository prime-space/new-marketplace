<?php namespace App\Product\Form\Constraint;

use App\Entity\Partnership;
use App\Entity\Product;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SufficientProductObjectAmountValidator extends ConstraintValidator
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SufficientProductObjectAmount) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\SufficientProductObjectAmount');
        }

        if (null === $value || '' === $value || 0 === $value) {
            return;
        }

        $productId = $this->context->getObject()->getParent()->get('productId')->getData();
        if (null === $productId) {
            return;
        }

        /** @var Product|null $product */
        $product = $this->repositoryProvider->get(Product::class)->findById($productId);
        if (null === $product) {
            return;
        }

        if (0 === $product->inStockNum) {
            $message = $constraint->messages[SufficientProductObjectAmount::MESSAGE_CODE_NOT_IN_STOCK];
            $this->context->buildViolation($message)->addViolation();
        } elseif ($value > $product->inStockNum) {
            $message = $constraint->messages[SufficientProductObjectAmount::MESSAGE_CODE_NOT_ENOUGHT];
            $this->context->buildViolation($message, ['%inStockNum%' => $product->inStockNum])->addViolation();
        }
    }
}
