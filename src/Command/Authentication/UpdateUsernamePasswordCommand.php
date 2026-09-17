<?php

namespace Code202\Security\Command\Authentication;

use Code202\Security\Service\Authentication\UsernamePasswordUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'code202:security:authentication:update-username-password',
    hidden: false,
    description: 'Creates a new authentication mode for account.'
)]
class UpdateUsernamePasswordCommand extends Command
{
    public function __construct(
        private readonly UsernamePasswordUpdater $updater
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('uuid', InputArgument::REQUIRED, 'The uuid of the account.')
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'The username for the authentication.')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The password for this authentication')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('username')) {
            $authentication = $this->updater->updateUsername($input->getArgument('uuid'), $input->getOption('username'));

            $output->writeln(sprintf('Username was updated for this authentication : %s', $authentication->getUuid()));
        }

        if ($input->getOption('password')) {
            $authentication = $this->updater->updatePassword($input->getArgument('uuid'), $input->getOption('password'));

            $output->writeln(sprintf('Password was updated for this authentication : %s', $authentication->getUuid()));
        }

        return Command::SUCCESS;
    }
}
