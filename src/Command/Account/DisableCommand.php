<?php

namespace Code202\Security\Command\Account;

use Code202\Security\Service\Account\Enabler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'code202:security:account:disable',
    hidden: false,
    description: 'Disable an account.'
)]
class DisableCommand extends Command
{
    public function __construct(
        private readonly Enabler $enabler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to disable an account.')
            ->addArgument('uuid', InputArgument::REQUIRED, 'The uuid of the account.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $input->getArgument('uuid');

        $this->enabler->disable($uuid);

        $output->writeln(sprintf('The account with uuid : %s was enabled', $uuid));

        return Command::SUCCESS;
    }
}
