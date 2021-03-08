<?php namespace App\Crud\Unit\AgentOffer\CustomAction;

use App\Crud\Unit\AgentOffer\AgentOfferCrudUnit;
use App\Entity\Event;
use App\Entity\Partnership;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Repository\UserRepository;
use Ewll\CrudBundle\Unit\CustomActionTargetInterface;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use Exception;
use RuntimeException;

class Accept implements CustomActionTargetInterface
{
    private $repositoryProvider;
    private $authenticator;
    private $defaultDbClient;
    private $eventFactory;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        DbClient $defaultDbClient,
        EventFactory $eventFactory
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->authenticator = $authenticator;
        $this->defaultDbClient = $defaultDbClient;
        $this->eventFactory = $eventFactory;
    }

    public function getName(): string
    {
        return 'accept';
    }

    public function getUnitName(): string
    {
        return AgentOfferCrudUnit::NAME;
    }

    /**
     * @inheritDoc
     * @param Partnership $entity
     */
    public function action($entity, array $data): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new RuntimeException('User is expected here');
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->repositoryProvider->get(User::class);
        $partnershipRepository = $this->repositoryProvider->get(Partnership::class);

        $entity->statusId = Partnership::STATUS_ID_OK;

        $this->defaultDbClient->beginTransaction();
        try {
            $partnershipRepository->update($entity, ['statusId']);
            $this->eventFactory->create(
                $entity->sellerUserId,
                Event::TYPE_ID_PARTNERSHIP_OFFER_ACCEPTED,
                $entity->id,
                ['agentName' => $user->getName()]
            );
            $userRepository->changeAgentPartnersNum($entity->agentUserId, UserRepository::PARTNERS_NUM_INCREASE);
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }
}
