<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'app:user:create';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create new User')
            ->addArgument('email', InputArgument::REQUIRED, 'email')
            ->addArgument('password', InputArgument::REQUIRED, 'password')
            ->addOption('roles', 'r', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'roles (multiple allowed)')
            ->addOption('displayName', 'd', InputOption::VALUE_REQUIRED, 'display name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $roles = $input->getOption('roles');
        $displayName = $input->getOption('displayName');

        $user = new User();
        $user->email = $email;
        $user->plainPassword = $password;
        $user->setRoles($roles ?: ['ROLE_USER']);
        $user->displayName = $displayName;

        $this->em->persist($user);
        $this->em->flush();

        $io->success('User is created');

        return 0;
    }
}
