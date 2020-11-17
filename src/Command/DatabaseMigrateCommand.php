<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseMigrateCommand extends Command
{
    protected static $defaultName = 'app:database:migrate';

    private $username;
    private $password;
    private $database;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->username = $connection->getUsername();
        $this->password = $connection->getPassword();
        $this->database = $connection->getDatabase();
    }

    protected function configure()
    {
        $this
            ->setDescription('run mysqldump before doctrine:migrations:migrate')
            ->addOption('dry-run', '', InputOption::VALUE_NONE, 'Execute the migration as a dry run.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pathToSave = sprintf('%s/docs/mysqldump/%s.sql', realpath(__DIR__.'/../..'), date('YmdHis'));

        $process = Process::fromShellCommandline(sprintf('mysqldump -u%s -p%s %s > %s', $this->username, $this->password, $this->database, $pathToSave));
        $process->setTimeout(60);
        $process->run();
        $this->ensureSuccessful($process);

        $io->success(sprintf('saved mysqldump to "%s"', $pathToSave));

        $migrationCommand = $this->getApplication()->find('doctrine:migrations:migrate');

        // pass options to doctrine:migrations:migrate command
        $arguments = [];
        foreach ($this->getDefinition()->getOptions() as $inputOption) {
            $arguments['--'.$inputOption->getName()] = $input->getOption($inputOption->getName());
        }
        $migrationInput = new ArrayInput($arguments);
        // @see https://stackoverflow.com/questions/52119220/symfony-command-no-interaction-is-not-working
        $migrationInput->setInteractive(!$input->getOption('no-interaction'));

        return $migrationCommand->run($migrationInput, $output);
    }

    private function ensureSuccessful(Process $process)
    {
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
