<?php namespace App\Account;

//use App\Constraints\Account as AccountConstraint;
//use App\Constraints\Accuracy;
//use App\Constraints\Password;
//use App\Constraints\PayoutInternalUsersId;
//use App\Constraints\PayoutMethod as PayoutMethodConstraint;
//use App\Constraints\PayoutReceiver;
//use App\Constraints\UserBlock;
use App\Account\Exception\InsufficientFundsException;
use App\Entity\Account;

//use App\Entity\PaymentSystem;
//use App\Entity\Payout;
//use App\Entity\PayoutMethod;
//use App\Entity\PayoutSet;
//use App\Entity\SystemAddBalance;
use App\Entity\Currency;
use App\Entity\Payout;
use App\Entity\PayoutMethod;
use App\Entity\Transaction;
use App\Entity\User;

//use App\Exception\InsufficientFundsException;
//use App\Form\Extension\Core\DataTransformer\PayoutMethodCodeToIdTransformer;
//use App\Form\Extension\Core\Type\VuetifyCheckboxType;
//use App\PaymentSystemManager\PaymentSystemManagerInterface;
//use App\PaymentSystemManager\PayoutInterface;
//use App\Repository\AccountRepository;
//use App\Repository\TransactionRepository;
//use App\TagServiceProvider\TagServiceProvider;
use App\MessageBroker\MessageBrokerConfig;
use App\Payout\PayoutMethodManagerInterface;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Exception\ExecuteException;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Ewll\UserBundle\Authenticator\Authenticator;
use Exception;
use RuntimeException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
//use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class Accountant
{
    const METHOD_CART_ITEM_SELLER = 1;
    const METHOD_CART_ITEM_PARTNER = 2;
    const METHOD_PAYOUT = 3;
    const METHOD_PAYOUT_REFUND = 4;
    const METHOD_SYSTEM_ADD = 5;
//    const METHOD_PAYMENT = 'payment';
//    const METHOD_PAYMENT_REFUND = 'paymentRefund';
//    const METHOD_VOUCHER = 'voucher';
//    const METHOD_PAYOUT = 'payout';
//    const METHOD_PAYOUT_SET = 'payoutSet';
//    const METHOD_PAYOUT_INCOME = 'payoutIncome';
//    const METHOD_PAYOUT_RETURN = 'payoutReturn';
//    const METHOD_SYSTEM = 'system';
//
//    const PAYOUT_FROM_FIELD_NAME_ID = 'id';
//    const PAYOUT_FROM_FIELD_NAME_RECEIVER = 'receiver';
//    const PAYOUT_FROM_FIELD_NAME_METHOD = 'method';
//    const PAYOUT_FROM_FIELD_NAME_ACCOUNT = 'accountId';
//    const PAYOUT_FROM_FIELD_NAME_AMOUNT = 'amount';
//    const PAYOUT_FROM_FIELD_NAME_PASSWORD = 'password';
//    const PAYOUT_FROM_FIELD_NAME_REMEMBER_PASSWORD = 'rememberPassword';

    private $repositoryProvider;
    private $messageBroker;
    private $defaultDbClient;
    private $translator;
    private $formFactory;
    private $authenticator;
    private $logger;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        MessageBroker $messageBroker,
        DbClient $defaultDbClient,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        Authenticator $authenticator,
        Logger $logger
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->messageBroker = $messageBroker;
        $this->defaultDbClient = $defaultDbClient;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->authenticator = $authenticator;
        $this->logger = $logger;
    }

//    /** @throws Exception */
//    public function systemAdd(Account $account, string $amount, string $comment): void
//    {
//        try {
//            $this->defaultDbClient->beginTransaction();
//
//            $systemAddBalance = SystemAddBalance::create($account->id, $amount, $comment);
//            $this->repositoryProvider->get(SystemAddBalance::class)->create($systemAddBalance);
//
//            $transaction = Transaction::create(
//                $account->userId,
//                $account->id,
//                self::METHOD_SYSTEM,
//                $systemAddBalance->id,
//                $amount,
//                $account->currencyId
//            );
//            $this->repositoryProvider->get(Transaction::class)->create($transaction);
//            $this->messageBroker->createMessage(MessageBroker::QUEUE_TRANSACTION_NAME, ['id' => $transaction->id], 5);
//
//            $this->defaultDbClient->commit();
//        } catch (Exception $e) {
//            $this->defaultDbClient->rollback();
//
//            throw $e;
//        }
//    }

    public function increase(
        int $userId,
        int $methodId,
        array $descriptionData,
        string $amount,
        int $currencyId,
        int $holdSeconds = 0
    ) {
        $account = $this->getPaymentAccountByUserIdAndCurrencyId($userId, $currencyId);
        $executingDateTime = new \DateTime("+$holdSeconds seconds");
        $transaction = Transaction::create($userId, $account->id, $methodId, $descriptionData, $amount, $currencyId,
            $executingDateTime);

        $this->repositoryProvider->get(Transaction::class)->create($transaction);

        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->repositoryProvider->get(Account::class);
        $accountRepository->addHoldByTransaction($transaction, AccountRepository::HOLD_INCREASE);

        $delay = $holdSeconds + 5;
        $this->messageBroker
            ->createMessage(MessageBrokerConfig::QUEUE_NAME_EXEC_TRANSACTION, ['id' => $transaction->id], $delay);
    }

    /** @throws InsufficientFundsException */
    public function payout(Payout $payout): void
    {
        /** @var PayoutMethod $payoutMethod */
        $payoutMethod = $this->repositoryProvider->get(PayoutMethod::class)->findById($payout->payoutMethodId);
        if (null === $payoutMethod) {
            throw new RuntimeException("PayoutManager #$payoutMethod->id is expect here");
        }
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->repositoryProvider->get(Account::class);
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->repositoryProvider->get(Transaction::class);

        /** @var Account|null $account */
        $account = $accountRepository->findById($payout->accountId, true);
        if (null === $account) {
            throw new RuntimeException('Account not found');
        }

        $isEnough = $this->isEnoughMoney($account->id, $payout->writeOff);
        if (!$isEnough) {
            throw new InsufficientFundsException();
        }

        $transactionAmount = bcmul($payout->writeOff, '-1', Currency::MAX_SCALE);

        $transaction = Transaction::create(
            $payout->userId,
            $account->id,
            self::METHOD_PAYOUT,
            ['payoutId' => $payout->id, 'payoutMethodName' => $payoutMethod->name, 'receiver' => $payout->receiver,],
            $transactionAmount,
            $account->currencyId
        );

        $transactionRepository->create($transaction);
        $this->messageBroker->createMessage(
            MessageBrokerConfig::QUEUE_NAME_EXEC_TRANSACTION,
            ['id' => $transaction->id],
            15
        );
        $this->messageBroker->createMessage(
            MessageBrokerConfig::QUEUE_NAME_SEND_PAYOUT,
            ['id' => $payout->id, 'attempt' => 1,],
            15
        );
    }

    public function refundPayout(Payout $payout)
    {
        /** @var Account $account */
        $account = $this->repositoryProvider->get(Account::class)->findById($payout->accountId);
        $transaction = Transaction::create(
            $payout->userId,
            $account->id,
            self::METHOD_PAYOUT_REFUND,
            ['payoutId' => $payout->id,],
            $payout->writeOff,
            $account->currencyId
        );
        $this->repositoryProvider->get(Transaction::class)->create($transaction);
        $this->messageBroker->createMessage(
            MessageBrokerConfig::QUEUE_NAME_EXEC_TRANSACTION,
            ['id' => $transaction->id],
            15
        );

    }

    /** @throws Exception */
    public function executeTransaction(Transaction $transaction)
    {
        bcscale(2);

        $transactionRepository = $this->repositoryProvider->get(Transaction::class);
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->repositoryProvider->get(Account::class);

        /** @var Account $account */
        $account = $accountRepository->findOneBy([
            'userId' => $transaction->userId,
            'currencyId' => $transaction->currencyId,
        ]);

        if (null === $account) {
            $account = Account::create($transaction->userId, $transaction->currencyId);
        }

        if ($account->lastTransactionId === Account::NO_LAST_TRANSACTION_ID) {
            $accountOperationId = 1;
            $oldBalance = '0';
        } else {
            /** @var Transaction $lastAccountTransaction */
            $lastAccountTransaction = $transactionRepository->findById($account->lastTransactionId);
            $oldBalance = $lastAccountTransaction->balance;
            $accountOperationId = $lastAccountTransaction->accountOperationId + 1;
        }

        $this->defaultDbClient->beginTransaction();
        try {
            $transaction->accountOperationId = $accountOperationId;
            $transaction->balance = bcadd($oldBalance, $transaction->amount);
            $account->lastTransactionId = $transaction->id;
            $account->balance = $transaction->balance;

            $transactionRepository->update($transaction);
            if (null === $account->id) {
                $accountRepository->create($account);
            } else {
                $accountRepository->update($account);
            }

            if (!in_array($transaction->methodId, [self::METHOD_PAYOUT, self::METHOD_PAYOUT_REFUND], true)) {
                $accountRepository->addHoldByTransaction($transaction, AccountRepository::HOLD_DECREASE);
            }
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();
            throw $e;
        }
    }

//    public function compileAccountsView(User $user): array
//    {
//        $accountsView = [];
//        /** @var Account[] $accounts */
//        $accounts = $this->repositoryProvider->get(Account::class)->findBy(['userId' => $user->id]);
//        foreach ($accounts as $account) {
//            $currencySign = $this->translator->trans("currency.$account->currencyId.sign", [], 'payment');
//            $accountsView[] = [
//                'id' => $account->id,
//                'balance' => $account->balance,
//                'currencyId' => $account->currencyId,
//                'currencySign' => $currencySign,
//            ];
//        }
//
//        return $accountsView;
//    }
//
//    public function getLKPayoutForm(): FormInterface
//    {
//        $formBuilder = $this->getPayoutFormBuilder('form');
//
//        $passConstraints = $this->authenticator->doNotAskPass() ? [] : [
//            new NotBlank(['message' => 'fill-field']),
//            new Password(),
//        ];
//
//        $formBuilder
//            ->add(self::PAYOUT_FROM_FIELD_NAME_PASSWORD, TextType::class, ['constraints' => $passConstraints])
//            ->add(self::PAYOUT_FROM_FIELD_NAME_REMEMBER_PASSWORD, VuetifyCheckboxType::class);
//
//        $form = $formBuilder->getForm();
//
//        return $form;
//    }

//    public function getApiPayoutForm(): FormInterface
//    {
//        $formBuilder = $this->getPayoutFormBuilder()
//            ->add(self::PAYOUT_FROM_FIELD_NAME_ID, IntegerType::class, ['constraints' => [
//                    new NotBlank(),
//                    new GreaterThanOrEqual(0),
//                    new PayoutInternalUsersId(),
//                ]
//            ]);
//
//        $form = $formBuilder->getForm();
//
//        return $form;
//    }

    public function getPaymentAccountByUserIdAndCurrencyId(int $userId, int $currencyId): Account
    {
        $accountRepository = $this->repositoryProvider->get(Account::class);

        /** @var Account $account */
        $account = $accountRepository->findOneBy([
            'userId' => $userId,
            'currencyId' => $currencyId,
        ]);

        if (null === $account) {
            $account = Account::create($userId, $currencyId);
            $accountRepository->create($account);
        }

        return $account;
    }

    public function calcWriteOff(string $amount, string $fee): string
    {
        $feeAmount = bcmul(bcdiv($fee, 100, 4), $amount, Currency::MAX_SCALE);
        $writeOff = bcadd($amount, $feeAmount, Currency::MAX_SCALE);

        return $writeOff;
    }

    public function isEnoughMoney(int $accountId, string $writeOff): bool
    {
        $realBalance = $this->getRealBalance($accountId);
        $isEnough = bccomp($realBalance, $writeOff, Currency::MAX_SCALE) > -1;

        return $isEnough;
    }

    public function getRealBalance(int $accountId): string
    {
        $transactionRepository = $this->repositoryProvider->get(Transaction::class);
        $unexecutedDecreaseTransactionSum = $transactionRepository
            ->calcUnexecutedDecreaseTransactionSum($accountId);
        $balance = $transactionRepository->getBalance($accountId);
        $realBalance = bcadd($balance, $unexecutedDecreaseTransactionSum, 8);

        return $realBalance;
    }

//    private function getPayoutFormBuilder(string $name = null): FormBuilderInterface
//    {
//        @TODO hardcode for debug
//        $userId = $this->authenticator->getUser()->id;
//        $maxAmount = $userId === 4 ? 100000 : 10000;
//
//        $formBuilder = $this->formFactory
//            ->createNamedBuilder($name, FormType::class, null, ['constraints' => [
//                new UserBlock,
//            ]])
//            ->add(self::PAYOUT_FROM_FIELD_NAME_RECEIVER, TextType::class, ['constraints' => [
//                new NotBlank(['message' => 'fill-field']),
//                new PayoutReceiver(),
//            ]])
//            @TODO 3 times for select PaymentMethod by code...
//            ->add(self::PAYOUT_FROM_FIELD_NAME_METHOD, TextType::class, ['constraints' => [
//                new NotBlank(['message' => 'fill-field']),
//                new PayoutMethodConstraint(),
//            ]])
//            ->add(self::PAYOUT_FROM_FIELD_NAME_ACCOUNT, IntegerType::class, ['constraints' => [
//                new NotBlank(['message' => 'fill-field']),
//                new AccountConstraint(),
//            ]])
//            ->add(self::PAYOUT_FROM_FIELD_NAME_AMOUNT, NumberType::class, ['label' => false, 'constraints' => [
//                new NotBlank(['message' => 'fill-field']),
//                new GreaterThanOrEqual(10),
//                new LessThanOrEqual($maxAmount),
//                new Accuracy(2),
//            ]]);
//
//        $formBuilder->get(self::PAYOUT_FROM_FIELD_NAME_METHOD)
//            ->addModelTransformer($this->payoutMethodCodeToIdTransformer);
//
//        return $formBuilder;
//    }

//    private function createPayout(PayoutSet $payoutSet, string $amount, string $fee, string $queueName): void
//    {
//        $feeAmount = $this->feeFetcher->calcFeeAmount($amount, $fee);
//        $credit = bcadd($amount, $feeAmount, 8);
//        $payout = Payout::create(
//            $payoutSet->id,
//            $amount,
//            $credit
//        );
//        $this->repositoryProvider->get(Payout::class)->create($payout);
//        $this->messageBroker->createMessage(
//            $queueName,
//            ['id' => $payout->id, 'try' => 1,],
//            15
//        );
//    }
}
