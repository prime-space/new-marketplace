<?php namespace App\Command;

use App\Daemon\SalesUpDaemon;
use App\Entity\Product;
use App\Entity\User;
use App\MessageBroker\MessageBrokerConfig;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MysqlMessageBrokerBundle\MessageBroker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SalesUpCommand extends Command
{
    const OPTION_USER_ID = 'userId';
    const OPTION_CYCLE_DAYS = 'cycleDays';
    const OPTION_UP_PERIOD_DAYS = 'upPeriodDays';
    const OPTION_APPROX_SALES_NUM = 'approxSalesNum';
    const OPTION_SALES_NUM_DISPERSION_PERCENT = 'salesNumDispersionPercent';

    private $repositoryProvider;
    private $logger;
    private $messageBroker;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        LoggerInterface $logger,
        MessageBroker $messageBroker
    ) {
        parent::__construct();
        $this->repositoryProvider = $repositoryProvider;
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
    }

    protected function configure()
    {
        $this
            ->addOption(self::OPTION_USER_ID, null, InputOption::VALUE_REQUIRED)
            ->addOption(self::OPTION_CYCLE_DAYS, null, InputOption::VALUE_REQUIRED, '', 30)
            ->addOption(self::OPTION_UP_PERIOD_DAYS, null, InputOption::VALUE_REQUIRED, '', 7)
            ->addOption(self::OPTION_APPROX_SALES_NUM, null, InputOption::VALUE_REQUIRED, '', 50)
            ->addOption(self::OPTION_SALES_NUM_DISPERSION_PERCENT, null, InputOption::VALUE_REQUIRED, '', 50);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $userId = (int)$input->getOption(self::OPTION_USER_ID);
        /** @var User|null $user */
        $user = $this->repositoryProvider->get(User::class)->findById($userId);
        if (null === $user) {
            $io->error("User #$userId not found");

            return 1;
        }
        $io->writeln("User #$userId {$user->getName()}");

        $cycleDays = (int)$input->getOption(self::OPTION_CYCLE_DAYS);
        $upPeriodDays = (int)$input->getOption(self::OPTION_UP_PERIOD_DAYS);
        $upPreiodSeconds = $upPeriodDays * 24 * 60 * 60;
        $approxSalesNum = (int)$input->getOption(self::OPTION_APPROX_SALES_NUM);
        $salesNumDispersionPercent = (int)$input->getOption(self::OPTION_SALES_NUM_DISPERSION_PERCENT);
        $salesNumDispersion = $salesNumDispersionPercent * $approxSalesNum / 100;
        $minSalesNum = $approxSalesNum - $salesNumDispersion;
        $maxSalesNum = $approxSalesNum + $salesNumDispersion;
        $io->writeln("Cycle $cycleDays days");
        $io->writeln("Up period $upPeriodDays days or $upPreiodSeconds seconds");
        $io->writeln(vsprintf(
            "Approx sales number %d, dispersion %d%% or %d, min %d, max %d",
            [$approxSalesNum, $salesNumDispersionPercent, $salesNumDispersion, $minSalesNum, $maxSalesNum]
        ));

        $productStatusesIds = [Product::STATUS_ID_OK, Product::STATUS_ID_OUT_OF_STOCK];
        /** @var Product[] $products */
        $products = $this->repositoryProvider->get(Product::class)->findBy([
            new FilterExpression(FilterExpression::ACTION_EQUAL, Product::FIELD_USER_ID, $user->id),
            new FilterExpression(FilterExpression::ACTION_IN, Product::FIELD_STATUS_ID, $productStatusesIds),
        ]);
        $numberOfProducts = count($products);
        $io->writeln("Number of products: $numberOfProducts");

        $io->writeln('');

        foreach ($products as $product) {
            $salesNum = rand($minSalesNum, $maxSalesNum);
            $io->writeln("Product #{$product->id}, sales number: $salesNum");
            for ($i=0; $i < $salesNum; $i++) {
                $delay = rand(1, $upPreiodSeconds);

                $this->messageBroker->createMessage(
                    MessageBrokerConfig::QUEUE_NAME_SALES_UP,
                    ['id' => $product->id, 'method' => SalesUpDaemon::METHOD_UP, 'cycle' => $cycleDays],
                    $delay
                );
            }

        }


        return 0;
    }
}
