<?php namespace App\Form\Constraint;

use App\Entity\Partnership;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PartnershipNotExistsOrRejectedValidator extends ConstraintValidator
{
    private $repositoryProvider;
    private $authenticator;

    public function __construct(RepositoryProvider $repositoryProvider, Authenticator $authenticator)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->authenticator = $authenticator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PartnershipNotExistsOrRejected) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\PartnershipNotExistsOrRejected');
        }

        if (null === $value || '' === $value) {
            return;
        }

        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }

        /** @var Partnership|null $partnership */
        $partnership = $this->repositoryProvider->get(Partnership::class)->findOneBy(
            ['agentUserId' => $value, 'sellerUserId' => $user->id]
        );

        if (null !== $partnership) {
            if ($partnership->statusId !== Partnership::STATUS_ID_REJECTED) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
