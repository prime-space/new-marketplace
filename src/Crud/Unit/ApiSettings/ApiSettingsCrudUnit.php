<?php namespace App\Crud\Unit\ApiSettings;

use App\Entity\User;
use App\Twofa\Action\SaveApiSettingsTwofaAction;
use Ewll\CrudBundle\Form\Extension\Core\Type\VuetifyCheckboxType;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\CrudBundle\Unit\UpdateMethodInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use Ewll\UserBundle\Form\Constraints\Twofa;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class ApiSettingsCrudUnit extends UnitAbstract implements
    UpdateMethodInterface
{
    const NAME = 'apiSettings';

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
        $config = new FormConfig(['data_class' => $this->getEntityClass()]);
        $config
            ->addField('isApiEnabled', VuetifyCheckboxType::class)
            ->addField('apiKey', FormType\PasswordType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(64),
                ],
                'empty_data' => $entity->apiKey,
            ])->addField('twofaCode', FormType\TextType::class, [
                'constraints' => [
                    new Twofa(SaveApiSettingsTwofaAction::CONFIG['id'])
                ],
                'mapped' => false,
            ]);

        return $config;
    }
}
