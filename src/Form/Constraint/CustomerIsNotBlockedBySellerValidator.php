<?php namespace App\Form\Constraint;

use App\Controller\CartController;
use App\Entity\Cart;
use App\Entity\Customer;
use App\Entity\CustomerBlockedEntity;
use App\Entity\Product;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerIsNotBlockedBySellerValidator extends ConstraintValidator
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CustomerIsNotBlockedBySeller) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CustomerIsNotBlockedBySeller');
        }

        if (null === $value || '' === $value) {
            return;
        }

        /** @var Product|null $product */
        $product = $this->repositoryProvider->get(Product::class)->findById($value);
        if (null === $product) {
            return;
        }

        /** @var FormInterface $form */
        $form = $this->context->getRoot();
        $email = $form->get(CartController::FORM_FIX_CART_FIELD_EMAIL)->getData();
        if (null === $email || '' === $email) {
            return;
        }
        /** @var Customer|null $customer */
        $customer = $this->repositoryProvider->get(Customer::class)->findOneBy([Customer::FIELD_EMAIL => $email]);
        if (null !== $customer) {
            /** @var CustomerBlockedEntity|null $customerBlockedEntityEmail */
            $customerBlockedEntityEmail = $this->repositoryProvider->get(CustomerBlockedEntity::class)->findOneBy([
                CustomerBlockedEntity::FIELD_BLOCKED_BY_USER_ID => $product->userId,
                CustomerBlockedEntity::FIELD_ENTITY_ID => $customer->id,
                CustomerBlockedEntity::FIELD_ENTITY_TYPE => CustomerBlockedEntity::TYPE_ID_EMAIL,
            ]);
            if (null !== $customerBlockedEntityEmail) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }

        /** @var Cart $cart */
        $cart = $this->repositoryProvider->get(Cart::class)->findById($constraint->cartId);
        /** @var CustomerBlockedEntity|null $customerBlockedEntityEmail */
        $customerBlockedEntityIp = $this->repositoryProvider->get(CustomerBlockedEntity::class)->findOneBy([
            CustomerBlockedEntity::FIELD_BLOCKED_BY_USER_ID => $product->userId,
            CustomerBlockedEntity::FIELD_ENTITY_ID => $cart->customerIpId,
            CustomerBlockedEntity::FIELD_ENTITY_TYPE => CustomerBlockedEntity::TYPE_ID_IP,
        ]);
        if (null !== $customerBlockedEntityIp) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }
    }
}
