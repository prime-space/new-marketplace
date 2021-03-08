<?php namespace App\Crud\Unit\PartnershipSellerAgent\CustomAction;

use App\Crud\Unit\PartnershipSellerAgent\PartnershipSellerAgentCrudUnit;
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

class Terminate implements CustomActionTargetInterface
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
        return 'terminate';
    }

    public function getUnitName(): string
    {
        return PartnershipSellerAgentCrudUnit::NAME;
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
        $entity->statusId = Partnership::STATUS_ID_REJECTED;


        $this->defaultDbClient->beginTransaction();
        try {
            $partnershipRepository->update($entity, ['statusId']);
            $this->eventFactory->create(
                $entity->agentUserId,
                Event::TYPE_ID_PARTNERSHIP_TERMINATED,
                $entity->id,
                ['userName' => $user->getName()]
            );
            $userRepository->changeAgentPartnersNum($entity->agentUserId, UserRepository::PARTNERS_NUM_DECREASE);
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }
}
