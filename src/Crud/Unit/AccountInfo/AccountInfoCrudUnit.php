<?php namespace App\Crud\Unit\AccountInfo;

use App\Entity\User;
use App\Form\Constraint\Unique;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class AccountInfoCrudUnit extends UnitAbstract implements
    UpdateMethodInterface
{
    const NAME = 'accountInfo';

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator
    ) {
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
        $isNickNameDisabled = $entity->nickname !== null;
        $config = new FormConfig(['data_class' => $this->getEntityClass()]);
        $config
            ->addField('id', FormType\TextType::class, ['disabled' => true,])
            ->addField('nickname', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5, 'max' => 24]),
                    new Unique(User::class, 'nickname', [], $entity->id, 'user.nickname.unique')
                ],
                'disabled' => $isNickNameDisabled,
            ])
            ->addField('contact', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 32]),
                    new Assert\Regex([
                        'message' => 'user.nickname.regex',
                        'pattern' => User::FIELD_NICKNAME_REGEX,
                    ]),
                ],
            ])
            ->addField('contactTypeId', FormType\ChoiceType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'choices' => User::CONTACT_TYPES,
            ]);

        return $config;
    }
}
