<?php namespace App\Crud\Unit\Event\CustomAction;

use App\Crud\Unit\Event\EventCrudUnit;
use App\Entity\Event;
use App\Repository\EventRepository;
use Ewll\CrudBundle\Unit\CustomActionTargetInterface;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use RuntimeException;

class MarkAsRead implements CustomActionTargetInterface
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
        return 'markAsRead';
    }

    public function getUnitName(): string
    {
        return EventCrudUnit::NAME;
    }

    /**
     * @inheritDoc
     * @param Event $entity
     */
    public function action($entity, array $data): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new RuntimeException('User is expected here');
        }

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->repositoryProvider->get(Event::class);

        $entity->isRead = true;
        $eventRepository->update($entity, ['isRead']);

        $haveUnreadEvent = $eventRepository->haveUnreadEvent($user->id);

        return ['haveUnreadEvent' => $haveUnreadEvent];
    }
}
