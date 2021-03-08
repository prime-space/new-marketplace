<?php namespace App\Repository;

use App\Account\Accountant;
use Ewll\DBBundle\Repository\Repository;

class TransactionRepository extends Repository
{
    public function findByUserIdForChartByMonthsOrDaysWithOffset(
        int $userId,
        string $timeOffset,
        bool $byMonths = false
    ): array {
        $prefix = 't1';
        $format = $byMonths ? '%m-%Y' : '%d-%m-%Y';
        $interval = $byMonths ? '12 MONTH' : '30 DAY';
        $formatMask = $byMonths ? '%Y-%m-01' : '%Y-%m-%d';
        $methodIdCartItemSeller = Accountant::METHOD_CART_ITEM_SELLER;
        $methodIdCartItemPartner = Accountant::METHOD_CART_ITEM_PARTNER;
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
SELECT 
    SUM(CASE 
           WHEN $prefix.methodId = $methodIdCartItemSeller 
           THEN 1 
           ELSE 0 
        END) AS 'sellerSalesNum',
    SUM(CASE 
           WHEN $prefix.methodId = $methodIdCartItemPartner 
           THEN 1 
           ELSE 0 
        END) AS 'partnerSalesNum',
    SUM($prefix.amount) AS 'amount',
    $prefix.currencyId,
    DATE_FORMAT(CONVERT_TZ($prefix.createdTs,'+00:00', '$timeOffset'), '$format') AS 'interval'
FROM {$this->config->tableName} $prefix
WHERE
    $prefix.createdTs > DATE_FORMAT(CONVERT_TZ(NOW(),'+00:00', '$timeOffset') - INTERVAL $interval, '$formatMask')
    AND $prefix.userId = :userId
    AND $prefix.methodId IN ($methodIdCartItemSeller, $methodIdCartItemPartner)
GROUP BY $prefix.currencyId, DATE_FORMAT(CONVERT_TZ($prefix.createdTs,'+00:00', '$timeOffset'), '$format')
ORDER BY
    $prefix.id;
SQL
            )
            ->execute(['userId' => $userId]);
        $result = $statement->fetchArrays();

        return $result;
    }

    public function calcUnexecutedDecreaseTransactionSum(int $accountId)
    {
        $prefix = 't1';
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
SELECT SUM(amount)
FROM {$this->config->tableName} $prefix
WHERE
    accountId = :accountId
    AND accountOperationId IS NULL
    AND amount < 0
SQL
            )
            ->execute([
                'accountId' => $accountId,
            ]);
        $sum = $statement->fetchColumn() ?? 0;

        return $sum;
    }

    public function getBalance(int $accountId)
    {
        $prefix = 't1';
        $statement = $this
            ->dbClient
            ->prepare(<<<SQL
SELECT balance
FROM {$this->config->tableName} $prefix
WHERE
    accountId = :accountId
    AND accountOperationId IS NOT NULL
ORDER BY accountOperationId DESC 
LIMIT 1
SQL
            )
            ->execute([
                'accountId' => $accountId,
            ]);
        $balance = $statement->fetchColumn() ?? 0;

        return $balance;
    }
}
