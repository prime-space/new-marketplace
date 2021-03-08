<?php namespace App\Crud\Unit\AgentOffer\CustomAction;

use App\Crud\Unit\AgentOffer\AgentOfferCrudUnit;
use App\Entity\Partnership;
use Ewll\CrudBundle\Unit\CustomActionTargetInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;

class Reject implements CustomActionTargetInterface
{
    private $repositoryProvider;
    private $authenticator;

    public function __construct(RepositoryProvider $repositoryProvider, Authenticator $authenticator)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->authenticator = $authenticator;
    }

    public function getName(): string
    {
        return 'reject';
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
        $partnershipRepository = $this->repositoryProvider->get(Partnership::class);

        $entity->statusId = Partnership::STATUS_ID_REJECTED;
        $partnershipRepository->update($entity, ['statusId']);

        return [];
    }
}
