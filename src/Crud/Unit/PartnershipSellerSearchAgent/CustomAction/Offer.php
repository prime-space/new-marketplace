<?php namespace App\Crud\Unit\PartnershipSellerSearchAgent\CustomAction;

use App\Crud\Unit\PartnershipSellerSearchAgent\PartnershipSellerSearchAgentCrudUnit;
use App\Entity\Event;
use App\Entity\Partnership;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Form\Constraint as CustomConstraint;
use App\Form\Constraint\PartnershipNotExistsOrRejected;
use Ewll\CrudBundle\Exception\ValidationException;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Form\FormErrorCompiler;
use Ewll\CrudBundle\Form\FormFactory;
use Ewll\CrudBundle\Unit\CustomActionMultipleInterface;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use Exception;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class Offer implements CustomActionMultipleInterface
{
    private $repositoryProvider;
    private $formFactory;
    private $formErrorCompiler;
    private $defaultDbClient;
    private $authenticator;
    private $eventFactory;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FormFactory $formFactory,
        FormErrorCompiler $formErrorCompiler,
        DbClient $defaultDbClient,
        Authenticator $authenticator,
        EventFactory $eventFactory
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->formFactory = $formFactory;
        $this->formErrorCompiler = $formErrorCompiler;
        $this->defaultDbClient = $defaultDbClient;
        $this->authenticator = $authenticator;
        $this->eventFactory = $eventFactory;
    }

    public function getName(): string
    {
        return 'offer';
    }

    public function getUnitName(): string
    {
        return PartnershipSellerSearchAgentCrudUnit::NAME;
    }

    /**
     * @inheritDoc
     * @param $entity User
     */
    public function action(array $data): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }
        $formConfig = new FormConfig([
            'constraints' => [new CustomConstraint\NicknameIsSet($user)],
        ]);
        $formConfig
            ->addField('fee', FormType\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0, 'max' => 90]),
                    new CustomConstraint\Accuracy(2),
                ],
            ])
            ->addField('agentUserId', FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\NotEqualTo($user->id),
                    new PartnershipNotExistsOrRejected(),
                ],
            ]);
        $form = $this->formFactory->create($formConfig);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = $this->formErrorCompiler->compile($form);

            throw new ValidationException($errors);
        }
        $formData = $form->getData();

        $partnershipRepository = $this->repositoryProvider->get(Partnership::class);

        /** @var Partnership|null $partnership */
        $partnership = $partnershipRepository->findOneBy(
            ['agentUserId' => $formData['agentUserId'], 'sellerUserId' => $user->id]
        );


        $this->defaultDbClient->beginTransaction();
        try {
            if (null === $partnership) {
                $partnership = Partnership::create($user->id, $formData['agentUserId'], $formData['fee']);
                $partnershipRepository->create($partnership);
            } else {
                $partnership->statusId = Partnership::STATUS_ID_OFFER;
                $partnershipRepository->update($partnership, ['statusId']);
            }
            $this->eventFactory->create(
                $partnership->agentUserId,
                Event::TYPE_ID_PARTNERSHIP_OFFER,
                $partnership->id,
                ['sellerName' => $user->getName()]
            );

            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }
}
