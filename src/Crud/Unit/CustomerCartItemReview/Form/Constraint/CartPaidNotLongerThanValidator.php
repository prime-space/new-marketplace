<?php namespace App\Crud\Unit\CustomerCartItemReview\Form\Constraint;

use App\Entity\Cart;
use App\Entity\CartItem;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CartPaidNotLongerThanValidator extends ConstraintValidator
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
        if (!$constraint instanceof CartPaidNotLongerThan) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CartPaidNotLongerThan');
        }

        $request = $this->requestStack->getCurrentRequest();
        $cartItemId = (int)($request->request->get('form', [])['cartItemId'] ?? 0);
        /** @var CartItem|null $cartItem */
        $cartItem = $this->repositoryProvider->get(CartItem::class)->findById($cartItemId);
        if (null !== $cartItem) {
            /** @var Cart $cart */
            $cart = $this->repositoryProvider->get(Cart::class)->findById($cartItem->cartId);
            if ($cart->isExpiredForReview()) {
                $params = ['%days%' => Cart::REVIEW_PUBLISHING_EXPIRATION_DAYS];
                $this->context->buildViolation($constraint->message, $params)->addViolation();
            }
        }
    }
}
