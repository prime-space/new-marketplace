<?php namespace App\Crud\Unit\Transaction;

use App\Account\Accountant;
use App\Chart\ChartDataCompiler;
use App\Entity\Account;
use App\Entity\Transaction;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'transaction';

    private $accountant;
    private $translator;
    private $chartDataCompiler;


    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        Accountant $accountant,
        TranslatorInterface $translator,
        ChartDataCompiler $chartDataCompiler
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->accountant = $accountant;
        $this->translator = $translator;
        $this->chartDataCompiler = $chartDataCompiler;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Transaction::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'userId', $user->id),
        ];
    }

    public function getReadListFields(): array
    {
        $currencyPlaceholder = sprintf('currency.%s.sign', ReadTransformer\Translate::PLACEHOLDER);

        return [
            'methodId',
            'descriptionData',
            'amount' => [new ReadTransformer\Money('amount', true)],
            'currency' => [new ReadTransformer\Translate('currencyId', 'payment', $currencyPlaceholder)],
            'dateCreate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE_TIME),
            'applying' => function (Transaction $transaction) {
                if ($transaction->isExecuted()) {
                    return 'Зачислено';
                } else {
                    return new ReadTransformer\Date('executingTs', ReadTransformer\Date::FORMAT_SHORT_DATE_TIME);
                }
            }
        ];
    }

    public function getReadListExtraData(array $context): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }
        $account = $this->accountant->getPaymentAccountByUserIdAndCurrencyId($user->id, Account::DEFAULT_CURRENCY_ID);

        return [
            'account' => $account->compilePrivateBalanceView($this->translator),
            'chart' => $this->chartDataCompiler->compileTransactionChartData($user),
        ];
    }
}
