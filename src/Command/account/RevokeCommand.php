<?php

namespace Code202\Security\Command\Account;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Code202\Security\Service\Account\RoleManipulator;

#[AsCommand(
    name: 'code202:security:account:revoke',
    hidden: false
)]
class RevokeCommand extends Command
{
    private RoleManipulator $manipulator;

    public function __construct(
        RoleManipulator $manipulator
    ) {
        parent::__construct();

        $this->manipulator = $manipulator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Revoke role for an account.')
            ->setHelp('This command allows you to revoke a new role for an account.')
            ->addArgument('uuid', InputArgument::REQUIRED, 'The uuid of the account.')
            ->addArgument('role', InputArgument::REQUIRED, 'The name of the role.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $input->getArgument('uuid');
        $role = $input->getArgument('role');

        $this->manipulator->revoke($uuid, $role);

        $output->writeln(sprintf('The role %s was revoked to account with uuid : %s', $role, $uuid));

        return Command::SUCCESS;
    }
}
