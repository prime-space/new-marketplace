<?php namespace App\Repository;

use App\Entity\Product;
use Ewll\DBBundle\Repository\Repository;

class EventRepository extends Repository
{
    public function getByProductVerification(): array
    {
        $productVerificationStatusId = Product::STATUS_ID_VERIFICATION;
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
SELECT u.id, u.email, COUNT(p.id) AS productNum
FROM user u
LEFT JOIN product p ON p.userId = u.id
WHERE
    p.statusId = $productVerificationStatusId
GROUP BY u.id, u.email
ORDER BY p.id DESC
SQL
            )
            ->execute();

        $items = $statement->fetchArrays();

        return $items;
    }

    public function haveUnreadEvent(int $userId): bool
    {
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
SELECT id
FROM event e
WHERE
    e.userId = :userId
    AND e.isRead = 0
LIMIT 1
SQL
            )
            ->execute(['userId' => $userId]);

        $items = $statement->fetchArrays();

        return count($items) > 0;
    }

    public function markAllAsRead(int $userId): void
    {
        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE event
SET isRead = 1
WHERE 
    userId = :userId
    AND isRead = 0
SQL
            )
            ->execute(['userId' => $userId]);
    }
}
