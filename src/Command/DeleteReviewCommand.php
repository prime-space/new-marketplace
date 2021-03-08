<?php namespace App\Command;

use App\Entity\Review;
use App\Product\ProductReviewStatActualizer;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteReviewCommand extends Command
{
    private $repositoryProvider;
    private $defaultDbClient;
    private $productReviewStatActualizer;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        DbClient $defaultDbClient,
        ProductReviewStatActualizer $productReviewStatActualizer
    ) {
        parent::__construct();
        $this->repositoryProvider = $repositoryProvider;
        $this->defaultDbClient = $defaultDbClient;
        $this->productReviewStatActualizer = $productReviewStatActualizer;
    }

    protected function configure()
    {
        $this
            ->addArgument('reviewId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reviewId = $input->getArgument('reviewId');
        $reviewRepository = $this->repositoryProvider->get(Review::class);
        /** @var Review $review */
        $review = $reviewRepository->findById($reviewId);
        if (null === $review) {
            throw new \RuntimeException("Review #{$reviewId} not found");
        }
        if ($review->isDeleted) {
            throw new \RuntimeException("Review #{$reviewId} already deleted");
        }

        $this->defaultDbClient->beginTransaction();
        try {
            $reviewRepository->delete($review);
            $this->productReviewStatActualizer->actualize($review->productId);
        } catch (\Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }
        $this->defaultDbClient->commit();

        $output->writeln("Review #{$reviewId} successful deleted");

        return 0;
    }
}
