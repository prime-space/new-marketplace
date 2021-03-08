<?php namespace App\Command;

use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MailerBundle\Mailer;
use Ewll\MailerBundle\Template;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Loader\FilesystemLoader;

class TestCommand extends Command
{
    private $repositoryProvider;
    private $mailer;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Mailer $mailer
    ) {
        parent::__construct();
        $this->repositoryProvider = $repositoryProvider;
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $template = new Template(
            'letterOrderCustomer',
            FilesystemLoader::MAIN_NAMESPACE,
            []
        );
        $this->mailer->create('343604@gmail.com', $template);

        return 0;
    }
}
