<?php namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpEnvCommand extends Command
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = require($this->projectDir . '/.env.local.php');
        $json = json_encode($config, JSON_HEX_QUOT | JSON_HEX_APOS);
        file_put_contents($this->projectDir . '/.env.local.json', $json, LOCK_EX);

        return 0;
    }
}
