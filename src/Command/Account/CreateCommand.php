<?php

namespace Code202\Security\Command\Account;

use Code202\Security\Service\Account\Creator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'code202:security:account:create',
    hidden: false,
    description: 'Creates a new account.',
    help: <<<'TXT'
This command allows you to create an account.
TXT
)]
class CreateCommand extends Command
{
    public function __construct(
        private readonly Creator $creator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the account.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $account = $this->creator->create($input->getArgument('name'));

        $output->writeln(sprintf('Account created with uuid : %s', $account->getUuid()));

        return Command::SUCCESS;
    }
}
