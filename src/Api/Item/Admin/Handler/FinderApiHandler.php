<?php namespace App\Api\Item\Admin\Handler;

use App\Api\Item\Admin\Finder\FinderEntityView;
use App\Api\Item\Admin\Finder\IdFinder;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\HttpFoundation\Request;

class FinderApiHandler
{
    private $repositoryProvider;
    /** @var IdFinder[] */
    private $findersById;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        iterable $findersById
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->findersById = $findersById;
    }

    public function find(Request $request): array
    {
        $entityViews = [];

        $query = $request->request->get('query');
        $filteredId = filter_var($query, FILTER_VALIDATE_INT) ?: null;
        if ($filteredId === null) {
        } else {
            foreach ($this->findersById as $finderById) {
                $views = $finderById->findById($filteredId);
                $shortClassName = $this->parseShortClassName($finderById->getEntityClass());
                $this->addEntityFindViews($entityViews, $shortClassName, $views);
            }
        }

        return $entityViews;
    }

    private function parseShortClassName(string $entityClass): string
    {
        $shortClassName = substr(strrchr($entityClass, "\\"), 1);

        return $shortClassName;
    }

    /** @param $views FinderEntityView[] */
    private function addEntityFindViews(array &$entityViews, string $shortClassName, $views)
    {
        if (!array_key_exists($shortClassName, $entityViews)) {
            $entityViews[$shortClassName]['views'] = [];
        }
        foreach ($views as $view) {
            $entityViews[$shortClassName]['views'][] = $view->toArray();
        }
    }
}
