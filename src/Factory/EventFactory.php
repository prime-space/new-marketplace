<?php namespace App\Factory;

use App\Entity\Event;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;

class EventFactory
{
    private $repositoryProvider;
    private $authenticator;

    public function __construct(RepositoryProvider $repositoryProvider, Authenticator $authenticator)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->authenticator = $authenticator;
    }

    public function create(int $userId, int $typeId, int $referenceId, $data = [])
    {
        $event = Event::create($userId, $typeId, $referenceId, $data);
        $this->repositoryProvider->get(Event::class)->create($event);
    }
}
