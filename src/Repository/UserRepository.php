<?php namespace App\Repository;

use App\Entity\Product;
use Ewll\DBBundle\Repository\Repository;
use RuntimeException;

class UserRepository extends Repository
{
    const PARTNERS_NUM_INCREASE = 1;
    const PARTNERS_NUM_DECREASE = 2;

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
    AND p.isDeleted = 0
GROUP BY u.id, u.email
ORDER BY p.id DESC
SQL
            )
            ->execute();

        $items = $statement->fetchArrays();

        return $items;
    }

    public function changeAgentPartnersNum(int $userId, int $action): void
    {
        if (!in_array($action, [self::PARTNERS_NUM_INCREASE, self::PARTNERS_NUM_DECREASE], true)) {
            throw new RuntimeException('Unknown action');
        }

        $actionSign = $action === self::PARTNERS_NUM_INCREASE ? '+' : '-';

        $this
            ->dbClient
            ->prepare(<<<SQL
UPDATE user
SET agentPartnershipsNum = agentPartnershipsNum $actionSign 1
WHERE id = :userId
SQL
            )
            ->execute(['userId' => $userId]);
    }
}
