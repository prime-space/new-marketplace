<?php namespace App\Command;

use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\LogExtraDataBundle\LogExtraDataKeeper;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

abstract class AbstractCommand extends Command
{
    /** @var RepositoryProvider */
    protected $repositoryProvider;
    /** @var LogExtraDataKeeper */
    protected $logExtraDataKeeper;
    /** @var Logger */
    protected $logger;

    public function setLogExtraDataKeeper(LogExtraDataKeeper $logExtraDataKeeper): void
    {
        $this->logExtraDataKeeper = $logExtraDataKeeper;
    }

    public function setRepositoryProvider(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logExtraDataKeeper->setData([
            'name' => $this->getName(),
            'session' => uniqid(),
        ]);

        $this->logger->info('Start command');

        $store = new FlockStore();
        $factory = new LockFactory($store);
        $lock = $factory->createLock(md5(serialize($input->getArguments())));

        if (!$lock->acquire()) {
            $this->logger->critical('Sorry, cannot lock file');

            return 1;
        }
        $result = $this->do($input, $output);
        $this->logger->info("Command has successfully finished");

        return $result;
    }

    abstract protected function do(InputInterface $input, OutputInterface $output);
}
