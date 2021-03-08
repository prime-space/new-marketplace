<?php namespace App\Crud\Unit\Payout;

use App\Account\Accountant;
use App\Account\Exception\InsufficientFundsException;
use App\Crud\Unit\Payout\Form\Constraint\EnoughMoney;
use App\Crud\Unit\Payout\Form\Constraint\PayoutReceiver;
use App\Entity\Account;
use App\Entity\Payout;
use App\Entity\PayoutMethod;
use App\Form\Constraint\Accuracy;
use App\Twofa\Action\AddPayoutTwofaAction;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Constraint\EntityAccess;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Entity;
use Ewll\CrudBundle\Unit\CreateMethodInterface;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use Ewll\UserBundle\Form\Constraints\Twofa;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class PayoutCrudUnit extends UnitAbstract implements
    ReadMethodInterface,
    CreateMethodInterface
{
    const NAME = 'payout';

    const FORM_FIELD_RECEIVER = 'receiver';
    const FORM_FIELD_METHOD = 'payoutMethodId';
    const FORM_FIELD_ACCOUNT = 'accountId';
    const FORM_FIELD_AMOUNT = 'amount';

    private $translator;
    private $accountant;


    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        TranslatorInterface $translator,
        Accountant $accountant
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->translator = $translator;
        $this->accountant = $accountant;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Payout::class;
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

    public function getReadOneFields(): array
    {
        return [];
    }

    public function getReadListFields(): array
    {
        $statusPlaceholder = sprintf('status.client-view.%s', ReadTransformer\Translate::PLACEHOLDER);
        $currencyPlaceholder = sprintf('currency.%s.sign', ReadTransformer\Translate::PLACEHOLDER);
        return [
            'id',
            'methodName' => new Entity('payoutMethodId', PayoutMethod::class, 'name'),
            'status' => [new ReadTransformer\Translate('statusId', 'payout', $statusPlaceholder)],
            'currency' => [
                new Entity('accountId', Account::class),
                new ReadTransformer\Translate('currencyId', 'payment', $currencyPlaceholder)
            ],
            'amount' => [new ReadTransformer\Money('amount', true)],
            'writeOff' => [new ReadTransformer\Money('writeOff', true)],
            'fee',
            'receiver',
            'dateCreate' => new ReadTransformer\Date('createdTs', ReadTransformer\Date::FORMAT_DATE_TIME),
        ];
    }

    public function getCreateFormConfig(): FormConfig
    {

        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [],
        ]);
        $config
            ->addField(self::FORM_FIELD_METHOD, FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new EntityAccess(PayoutMethod::class),
                ],
                'choices' => $this->getMethodChoices(),
                'label' => 'Платежное направление',
            ])
            ->addField(self::FORM_FIELD_RECEIVER, FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new PayoutReceiver(),
                ],
                'label' => 'Номер кошелька получателя',
            ])
            ->addField(self::FORM_FIELD_ACCOUNT, FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'choices' => $this->getAccountChoices(),
                'label' => 'Счет',
            ])
            ->addField(self::FORM_FIELD_AMOUNT, FormType\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 10, 'max' => 10000]),
                    new Accuracy(2),
                    new EnoughMoney(),
                ],
                'label' => 'Сумма зачисления',
            ])
            ->addField('twofaCode', FormType\TextType::class, [
                'constraints' => [
                    new Twofa(AddPayoutTwofaAction::CONFIG['id'])
                ],
                'mapped' => false,
            ]);

        return $config;
    }

    /** @param $entity Payout */
    public function getMutationsOnCreate(object $entity): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }
        /** @var PayoutMethod $payoutMethod */
        $payoutMethod = $this->repositoryProvider->get(PayoutMethod::class)->findById($entity->payoutMethodId);
        $mutations = [
            'userId' => $user->id,
            'fee' => $payoutMethod->fee,
            'writeOff' => $this->accountant->calcWriteOff($entity->amount, $payoutMethod->fee),
        ];

        return $mutations;
    }

    /** @param $entity Payout */
    public function onCreate(object $entity, array $formData): void
    {
        try {
            $this->accountant->payout($entity);
        } catch (InsufficientFundsException $e) {
            throw new \RuntimeException('InsufficientFundsException', 0, $e);
        }
    }

    /** @param $entity Payout */
    public function getCreateExtraData(object $entity): array
    {
        /** @var Account $account */
        $account = $this->repositoryProvider->get(Account::class)->findById($entity->accountId);
        $balance = $this->accountant->getRealBalance($entity->accountId);

        return ['accountSelectText' => $this->compileAccountSelectText($account->id, $account->currencyId, $balance)];
    }

    private function getMethodChoices(): array
    {
        /** @var PayoutMethod[] $methods */
        $methods = $this->repositoryProvider->get(PayoutMethod::class)->findAll();
        $items = [];
        foreach ($methods as $method) {
            $items["$method->name ($method->fee%)"] = $method->id;
        }

        return $items;
    }

    private function getAccountChoices()
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }

        /** @var Account[] $accounts */
        $accounts = $this->repositoryProvider->get(Account::class)->findBy([Account::FIELD_USER_ID => $user->id]);
        $items = [];
        foreach ($accounts as $account) {
            $text = $this->compileAccountSelectText($account->id, $account->currencyId, $account->balance);
            $items[$text] = $account->id;
        }

        return $items;
    }

    private function compileAccountSelectText(int $accountId, int $currencyId, string $balance): string
    {
        $currencySign = $this->translator->trans("currency.$currencyId.sign", [], 'payment');
        $balance = number_format($balance, 2);

        return "#$accountId - $balance $currencySign";
    }
}
