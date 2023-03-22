<?php

namespace Code202\Security\Command\Account;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Code202\Security\Service\Account\Enabler;

#[AsCommand(
    name: 'code202:security:account:disable',
    hidden: false
)]
class DisableCommand extends Command
{
    private Enabler $enabler;

    public function __construct(
        Enabler $enabler
    ) {
        parent::__construct();

        $this->enabler = $enabler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Disable an account.')
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
