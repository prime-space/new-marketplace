<?php namespace App\Crud\Unit\CustomerCartItemReview\Form\Constraint;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Review;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CartItemNoHaveReviewValidator extends ConstraintValidator
{
    private $repositoryProvider;
    private $requestStack;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        RequestStack $requestStack
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->requestStack = $requestStack;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CartItemNoHaveReview) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CartItemNoHaveReview');
        }

        $request = $this->requestStack->getCurrentRequest();
        $cartItemId = (int)($request->request->get('form', [])['cartItemId'] ?? 0);
        /** @var Review|null $review */
        $review = $this->repositoryProvider->get(Review::class)->findOneBy([
            Review::FIELD_CART_ITEM_ID => $cartItemId,
            Review::FIELD_IS_DELETED => 0,
        ]);
        if (null !== $review) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
