<?php namespace App\Repository;

use App\Entity\Ticket;
use Ewll\DBBundle\Repository\Repository;

class TicketRepository extends Repository
{
    const ACTION_INCREASE = 1;
    const ACTION_DECREASE = 2;

    public function increaseMessagesSendingNum(Ticket $ticket, int $action): void
    {
        switch ($action) {
            case self::ACTION_INCREASE:
                $value = 1;
                break;
            case self::ACTION_DECREASE:
                $value = -1;
                break;
            default:
                throw new \RuntimeException('Unknown action');
        }
        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE ticket
SET messagesSendingNum = messagesSendingNum + :value
WHERE
    id = :id
SQL
            )
            ->execute(['id' => $ticket->id, 'value' => $value]);
    }

    public function increaseMessagesNum(Ticket $ticket): void
    {
        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE ticket
SET messagesNum = messagesNum + 1
WHERE
    id = :id
SQL
            )
            ->execute(['id' => $ticket->id]);
    }
}
