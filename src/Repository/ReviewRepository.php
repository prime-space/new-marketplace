<?php namespace App\Repository;

use Ewll\DBBundle\Repository\Repository;

class ReviewRepository extends Repository
{
    public function countReviews(int $productId): array
    {
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
SELECT COUNT(r.id), SUM(r.isGood)
FROM review r
WHERE
    r.isDeleted = 0
    AND r.productId = :productId
SQL
            )
            ->execute(['productId' => $productId]);

        $items = $statement->fetchArray();
        $items = array_map('intval', array_values($items));

        return $items;
    }
}
