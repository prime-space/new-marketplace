<?php namespace App\Command;

use App\Product\ProductReviewStatActualizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActualizeProductReviewsCommand extends Command
{
    private $productReviewStatActualizer;

    public function __construct(
        ProductReviewStatActualizer $productReviewStatActualizer
    )
    {
        parent::__construct();
        $this->productReviewStatActualizer = $productReviewStatActualizer;
    }

    protected function configure()
    {
        $this
            ->addArgument('productId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productId = $input->getArgument('productId');
        $this->productReviewStatActualizer->actualize($productId);

        return 0;
    }
}
