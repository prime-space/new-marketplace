<?php namespace App\Crud\Unit\Event\CustomAction;

use App\Crud\Unit\Event\EventCrudUnit;
use App\Entity\Event;
use App\Repository\EventRepository;
use Ewll\CrudBundle\Unit\CustomActionMultipleInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use RuntimeException;

class MarkAllAsRead implements CustomActionMultipleInterface
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
        return 'markAllAsRead';
    }

    public function getUnitName(): string
    {
        return EventCrudUnit::NAME;
    }

    /**
     * @inheritDoc
     */
    public function action(array $data): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new RuntimeException('User is expected here');
        }
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->repositoryProvider->get(Event::class);
        $eventRepository->markAllAsRead($user->id);

        return [];
    }
}
