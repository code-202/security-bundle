<?php

namespace Code202\Security\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Code202\Security\Service\Account\Creator;

#[AsCommand(
    name: 'code202:security:create-account',
    hidden: false
)]
class CreateAccountCommand extends Command
{
    private Creator $creator;

    public function __construct(
        Creator $creator
    ) {
        parent::__construct();

        $this->creator = $creator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new account.')
            ->setHelp('This command allows you to create an account.')
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
