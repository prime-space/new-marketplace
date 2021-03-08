<?php namespace App\Crud\Unit\Payout\Form\Constraint;

use App\Account\Accountant;
use App\Crud\Unit\Payout\PayoutCrudUnit;
use App\Entity\Account;
use App\Entity\PayoutMethod;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EnoughMoneyValidator extends ConstraintValidator
{
    private $repositoryProvider;
    private $accountant;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Accountant $accountant
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->accountant = $accountant;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EnoughMoney) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\EnoughMoney');
        }

        if (null === $value || '' === $value) {
            return;
        }

        $accountId = (int)$this->context->getRoot()->get(PayoutCrudUnit::FORM_FIELD_ACCOUNT)->getData();
        $payoutMethodId = (int)$this->context->getRoot()->get(PayoutCrudUnit::FORM_FIELD_METHOD)->getData();

        /** @var Account|null $account */
        $account = $this->repositoryProvider->get(Account::class)->findById($accountId);
        if (null === $account) {
            return;
        }

        /** @var PayoutMethod|null $payoutMethod */
        $payoutMethod = $this->repositoryProvider->get(PayoutMethod::class)->findById($payoutMethodId);
        if (null === $payoutMethod) {
            return;
        }

        $writeOff = $this->accountant->calcWriteOff($value, $payoutMethod->fee);
        if (!$this->accountant->isEnoughMoney($accountId, $writeOff)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
