<?php

namespace Code202\Security\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Code202\Security\Service\Authentication\UsernamePasswordCreator;

#[AsCommand(
    name: 'code202:security:create-authentication:username-password',
    hidden: false
)]
class CreateAuthenticationUsernamePasswordCommand extends Command
{
    private UsernamePasswordCreator $creator;

    public function __construct(
        UsernamePasswordCreator $creator
    ) {
        parent::__construct();

        $this->creator = $creator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new authentication mode for account.')
            ->addArgument('uuid', InputArgument::REQUIRED, 'The uuid of the account.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username for the authentication.')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The password for this authentication')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $authentication = $this->creator->create($input->getArgument('uuid'), $input->getArgument('username'), $input->getOption('password'));

        $output->writeln(sprintf('Username/Password authentication for this account was created with username : %s', $authentication->getUuid()));

        return Command::SUCCESS;
    }
}
