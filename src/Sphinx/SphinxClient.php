<?php namespace App\Sphinx;

use Ewll\DBBundle\DB\Client as DbClient;

class SphinxClient
{
    const ENTITY_PRODUCT = 'product';

    private $sphinxDbClient;

    public function __construct(
        DbClient $sphinxDbClient
    ) {
        $this->sphinxDbClient = $sphinxDbClient;
    }

    public function put(string $entity, $id, array $data, DbClient $client = null): void
    {
        $client = $client ?? $this->sphinxDbClient;
        $data['id'] = $id;
        $fieldNames = [];
        $placeholderNames = [];
        foreach ($data as $key => $value) {
            $fieldNames[] = $key;
            $placeholderNames[] = ":{$key}";
        }
        $fieldNamesStr = implode(', ', $fieldNames);
        $placeholderNamesStr = implode(', ', $placeholderNames);

        $client->prepare(<<<SQL
REPLACE INTO $entity
    ($fieldNamesStr)
VALUES
    ($placeholderNamesStr)
SQL
        )->execute($data);
    }

    public function find(string $entity, string $query): array
    {
        $query = substr($query, 0, 100);
        $explodedQuery = explode(' ', $query);
        $words = [];
        foreach ($explodedQuery as $queryPart) {
            $queryPart = trim($queryPart);
            if (!empty($queryPart)) {
                $words[] = $queryPart;
            }
        }

        if (count($words) === 0) {
            return [];
        }
        $match = implode(' | ', $words);

        $statement = $this->sphinxDbClient->prepare(<<<SQL
SELECT id
FROM $entity
WHERE MATCH(:match)
SQL
        )->execute(['match' => $match]);

        $data = $statement->fetchColumns();

        return $data;
    }
}

