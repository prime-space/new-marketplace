<?php namespace App\Command;

use App\Entity\Product;
use App\Sphinx\SphinxClient;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SphinxInitCommand extends Command
{
    private $repositoryProvider;
    private $sphinxClient;
    private $sphinxLocalDbClient;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        SphinxClient $sphinxClient,
        DbClient $sphinxLocalDbClient
    ) {
        parent::__construct();
        $this->repositoryProvider = $repositoryProvider;
        $this->sphinxClient = $sphinxClient;
        $this->sphinxLocalDbClient = $sphinxLocalDbClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productRepository = $this->repositoryProvider->get(Product::class);
        /** @var Product[] $products */
        $products = $productRepository->findBy([
            new FilterExpression(FilterExpression::ACTION_IS_NOT_NULL, 'name'),
        ]);
        //@TODO CHUNKS
        foreach ($products as $product) {
            $this->sphinxClient->put(
                SphinxClient::ENTITY_PRODUCT,
                $product->id,
                ['name' => $product->name],
                $this->sphinxLocalDbClient
            );
        }

        return 0;
    }
}
