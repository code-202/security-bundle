<?php

namespace Code202\Security\Service\Activity\Trigger;

use Code202\Security\Entity\Activity\Console;
use Code202\Security\Entity\Activity\Trigger;
use Code202\Security\Entity\Activity\TriggerConsole;
use Code202\Security\Entity\Activity\TriggerReference;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ConsoleCommandEvent::class, method: 'onConsoleCommandEvent')]
#[AsEventListener(event: ConsoleTerminateEvent::class, method: 'onConsoleTerminateEvent')]
class ConsoleProvider implements ProviderInterface
{
    protected ?string $runningCommandName = null;

    protected ?array $runningArguments = null;

    protected ?array $runningOptions = null;

    public function __construct(
        protected EntityManagerInterface $em
    ) {}

    public function onConsoleCommandEvent(ConsoleCommandEvent $event): void
    {
        $this->runningCommandName = $event->getCommand()?->getName();
        $this->runningArguments = $event->getInput()->getArguments();
        $this->runningOptions = $event->getInput()->getOptions();
    }

    public function onConsoleTerminateEvent(ConsoleTerminateEvent $event): void
    {
        $this->runningCommandName = null;
        $this->runningArguments = null;
        $this->runningOptions = null;
    }

    public function supports(): bool
    {
        return null !== $this->runningCommandName;
    }

    public function get(): Trigger
    {
        if (null === $this->runningCommandName) {
            throw new RuntimeException('there is no running command');
        }

        $repository = $this->em->getRepository(TriggerConsole::class);

        $res = $repository->findOneBy([]);

        if (!$res instanceof TriggerConsole) {
            $res = new TriggerConsole(new Console());
        }

        $res->setData('command', $this->runningCommandName);
        if ($this->runningArguments) {
            $res->setData('arguments', $this->runningArguments);
        }

        if ($this->runningOptions) {
            $res->setData('options', $this->runningOptions);
        }

        return $res;
    }

    public function findAll(TriggerReference $reference): array
    {
        if ($reference instanceof Console) {
            $repository = $this->em->getRepository(TriggerConsole::class);

            return $repository->findBy([]);
        }

        return [];
    }
}
