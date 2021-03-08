<?php namespace App\Crud\Source;

use App\Api\Item\Admin\Handler\AdminApiHandlerInterface;
use App\Api\Item\Admin\Handler\TicketApiHandler;
use App\Crud\Unit\TicketMessage\TicketMessageDto;
use App\Entity\Ticket;
use App\MessageBroker\MessageBrokerConfig;
use App\Repository\TicketRepository;
use Ewll\CrudBundle\Condition;
use Ewll\CrudBundle\Condition\RelationCondition;
use Ewll\CrudBundle\Source\ItemsList;
use Ewll\CrudBundle\Source\SourceInterface;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Symfony\Component\HttpFoundation\ParameterBag;

class AdminApiSource implements SourceInterface
{
    /** @var AdminApiHandlerInterface[] */
    private $adminApiHandlers;
    private $repositoryProvider;
    private $messageBroker;
    /**
     * @var DbClient
     */
    private $defaultDbClient;

    public function __construct(
        iterable $adminApiHandlers,
        RepositoryProvider $repositoryProvider,
        MessageBroker $messageBroker,
        DbClient $defaultDbClient
    ) {
        $this->adminApiHandlers = $adminApiHandlers;
        $this->repositoryProvider = $repositoryProvider;
        $this->messageBroker = $messageBroker;
        $this->defaultDbClient = $defaultDbClient;
    }

    /** @inheritDoc */
    public function getById(string $entityClassName, int $id, array $accessConditions): object
    {
        throw new \RuntimeException('Not realised');
    }

    public function create(object $item, callable $onCreate): void
    {
        if (get_class($item) === TicketMessageDto::class) {
            /** @var TicketRepository $ticketRepository */
            $ticketRepository = $this->repositoryProvider->get(Ticket::class);
            /** @var Ticket $ticket */
            $ticket = $ticketRepository->findById($item->ticketId);
            $data = [
                'serviveClass' => TicketApiHandler::class,
                'method' => 'createMessage',
                'args' => [$item->ticketId, $item->message]
            ];
            $this->defaultDbClient->beginTransaction();
            try {
                $ticketRepository->increaseMessagesNum($ticket);
                $ticketRepository->increaseMessagesSendingNum($ticket, TicketRepository::ACTION_INCREASE);
                $this->messageBroker->createMessage(MessageBrokerConfig::QUEUE_NAME_ADMIN_API_REQUEST, $data, 3);
                $onCreate();
                $this->defaultDbClient->commit();
            } catch (\Exception $e) {
                $this->defaultDbClient->rollback();

                throw new \RuntimeException("Transaction fail: {$e->getMessage()}", 0, $e);
            }
        } else {
            throw new \RuntimeException('Not realised');
        }
    }

    public function update(object $item, array $options, callable $onUpdate = null): void
    {
        throw new \RuntimeException('Not realised');
    }

    public function findOne(string $entityClassName, array $conditions): ?object
    {
        throw new \RuntimeException('Not realised');
    }

    public function findList(
        string $entityClassName,
        array $conditions,
        int $page,
        int $itemsPerPage,
        array $sort
    ): ItemsList {
        $filters = $this->convertConditionsToFilters($conditions);
        if ($entityClassName === TicketMessageDto::class) {
            /** @var TicketApiHandler $service */
            $service = $this->getAdminApiHandler(TicketApiHandler::class);
            $ticketId = $filters->getInt('ticketId');
            /** @var Ticket|null $ticket */
            $ticket = $this->repositoryProvider->get(Ticket::class)->findById($ticketId);
            if (null === $ticket) {
                throw new \RuntimeException("Ticket #$ticketId not found");
            }
            $items = $service->inverseGetMessages($ticket);
            $total = count($items);
            $dtoClass = TicketMessageDto::class;
        } else {
            throw new \RuntimeException('Not realised');
        }

        $itemDtos = [];
        foreach ($items as $item) {
            $itemDtos[] = $this->hydrateDto($dtoClass, $item);
        }
        $itemsList = new ItemsList($itemDtos, $total);

        return $itemsList;
    }

    private function getAdminApiHandler(string $className): AdminApiHandlerInterface
    {
        foreach ($this->adminApiHandlers as $adminApiHandler) {
            if ($adminApiHandler instanceof $className) {
                return $adminApiHandler;
            }
        }

        throw new \RuntimeException("AdminApiHandler '$className' not found");
    }

    /**
     * @param Condition\ConditionInterface[] $accessConditions
     */
    private function convertConditionsToFilters(array $accessConditions): ParameterBag
    {
        $filters = new ParameterBag();
        foreach ($accessConditions as $accessCondition) {
            if ($accessCondition instanceof Condition\ExpressionCondition) {
                $field = $accessCondition->getField();
                $value = $accessCondition->getValue();
                switch ($accessCondition->getAction()) {
                    case Condition\ExpressionCondition::ACTION_EQUAL:
                        if ($filters->has($field)) {
                            throw new \RuntimeException("Duplicate filter field '$field'");
                        }
                        $filters->set($field, $value);
                        break;
                    default:
                        throw new \RuntimeException('Unknown ExpressionCondition action');
                }
            } else {
                throw new \RuntimeException('Unknown Condition \'' . get_class($accessCondition) . '\'');
            }
        }

        return $filters;
    }

    private function hydrateDto(string $dtoClass, array $item): object
    {
        $dto = new $dtoClass;
        //@TODO validate data!!!
        foreach ($item as $key => $value) {
            $dto->$key = $value;
        }

        return $dto;
    }

    public function isEntityResolveRelationCondition(object $entity, RelationCondition $accessCondition): bool
    {
        throw new \RuntimeException('Not realised');
    }

    public function delete(object $item, bool $force, callable $onDelete): void
    {
        throw new \RuntimeException('Not realised');
    }
}
