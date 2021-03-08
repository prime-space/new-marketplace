<?php namespace App\Crud\Unit\Payout\Form\Constraint;

use App\Crud\Unit\Payout\PayoutCrudUnit;
use App\Entity\PayoutMethod;
use App\Entity\PayoutMethod as PayoutMethodEntity;
use App\Payout\Exception\PayoutReceiverValidationException;
use App\Payout\PayoutMethodManagerInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PayoutReceiverValidator extends ConstraintValidator
{
    private $repositoryProvider;
    /** @var PayoutMethodManagerInterface[] */
    private $payoutMethodManagers;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        iterable $payoutMethodManagers
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->payoutMethodManagers = $payoutMethodManagers;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PayoutReceiver) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\PayoutReceiver');
        }

        if (null === $value || '' === $value) {
            return;
        }

//        $accountId = (int)$this->context->getRoot()->get(PayoutCrudUnit::)->getData();
        $payoutMethodId = (int)$this->context->getRoot()->get(PayoutCrudUnit::FORM_FIELD_METHOD)->getData();

        $payoutMethod = $this->repositoryProvider->get(PayoutMethodEntity::class)->findById($payoutMethodId);
        if (null === $payoutMethod) {
            return;
        }

        $payoutManager = $this->findPayoutManager($payoutMethod);
        if (null === $payoutMethod) {
            return;
        }

        try {
//            $payoutManager->validateReceiver($value, $accountId);
            $payoutManager->validateReceiver($value);
        } catch (PayoutReceiverValidationException $e) {
            $this->context
                ->buildViolation($e->getMessage())
                ->addViolation();

            return;
        }
    }

    private function findPayoutManager(PayoutMethod $payoutMethod): ?PayoutMethodManagerInterface
    {
        foreach ($this->payoutMethodManagers as $payoutMethodManager) {
            if ($payoutMethodManager->getMethodId() === $payoutMethod->id) {
                return $payoutMethodManager;
            }
        }

        return null;
    }
}
