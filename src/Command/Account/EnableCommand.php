<?php

namespace Code202\Security\Command\Account;

use Code202\Security\Service\Account\Enabler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'code202:security:account:enable',
    hidden: false
)]
class EnableCommand extends Command
{
    public function __construct(
        private readonly Enabler $enabler
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Enable an account.')
            ->setHelp('This command allows you to enable an account.')
            ->addArgument('uuid', InputArgument::REQUIRED, 'The uuid of the account.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $input->getArgument('uuid');

        $this->enabler->enable($uuid);

        $output->writeln(sprintf('The account with uuid : %s was enabled', $uuid));

        return Command::SUCCESS;
    }
}
