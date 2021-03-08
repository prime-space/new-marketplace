<?php namespace App\Form\Constraint;

use App\Entity\Customer;
use App\Entity\CustomerBlockedEntity;
use App\Entity\Ip;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerBlockedEntityExistValidator extends ConstraintValidator
{
    private $repositoryProvider;
    private $requestStack;

    public function __construct(RepositoryProvider $repositoryProvider, RequestStack $requestStack)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->requestStack = $requestStack;
    }

    public function validate($value, Constraint $constraint)
    {
        $entityForm = $this->requestStack->getCurrentRequest()->get('form');
        $entityTypeId = $entityForm[CustomerBlockedEntity::FIELD_ENTITY_TYPE];
        if (!$constraint instanceof CustomerBlockedEntityExist) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CustomerBlockedEntityExist');
        }

        if (null === $value || '' === $value) {
            return;
        }

        /** @var Customer|Ip $entity */
        if ($entityTypeId == CustomerBlockedEntity::TYPE_ID_EMAIL) {
            $entity = $this->repositoryProvider->get(Customer::class)->findById($value);
        } elseif ($entityTypeId == CustomerBlockedEntity::TYPE_ID_IP) {
            $entity = $this->repositoryProvider->get(Ip::class)->findById($value);
        } else {
            throw new \LogicException('Invalid entity type');
        }

        if (null === $entity) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
