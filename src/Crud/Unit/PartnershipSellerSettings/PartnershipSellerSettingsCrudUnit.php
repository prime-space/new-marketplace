<?php namespace App\Crud\Unit\PartnershipSellerSettings;

use App\Entity\User;
use App\Form\Constraint\Accuracy;
use App\Form\Constraint\NicknameIsSet;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class PartnershipSellerSettingsCrudUnit extends UnitAbstract implements
    UpdateMethodInterface
{
    const NAME = 'partnershipSellerSettings';

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator
    )
    {
        parent::__construct($repositoryProvider, $authenticator);
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return User::class;
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
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'id', $user->id),
        ];
    }

    /** @param $entity User */
    public function getUpdateFormConfig(object $entity): FormConfig
    {
        $config = new FormConfig([
            'data_class' => $this->getEntityClass(),
            'constraints' => [new NicknameIsSet($this->getUser())],

        ]);
        $config
            ->addField('partnerSellActionId', FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'choices' => User::PARTNER_SELL_ACTIONS,
                'choice_translation_domain' => 'dictionary',
            ])
            ->addField('partnerDefaultFee', FormType\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0, 'max' => 90]),
                    new Accuracy(2),
                ],
                'empty_data' => '0',
            ]);

        return $config;
    }
}
