<?php

namespace Code202\Security\Service\Account;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Event\Account\DisabledEvent;
use Code202\Security\Event\Account\EnabledEvent;

class Enabler
{
    protected EntityManagerInterface $em;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function enable(Account|string $accountOrUuid, bool $autoFlush = true)
    {
        $account = $this->getAccount($accountOrUuid);

        $account->enable(true);

        $this->em->persist($account);

        $event = new EnabledEvent($account);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }

    public function disable(Account|string $accountOrUuid, bool $autoFlush = true)
    {
        $account = $this->getAccount($accountOrUuid);

        $account->disable(false);

        $this->em->persist($account);

        $event = new DisabledEvent($account);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }

    protected function getAccount(Account|string $accountOrUuid): Account
    {
        if ($accountOrUuid instanceof Account) {
            $account = $accountOrUuid;
        } else {
            $account = $this->em->getRepository(Account::class)->findOneBy([ 'uuid' => $accountOrUuid ]);
        }

        if (!$account) {
            throw new Exception\RoleManipulator(sprintf('Account not found for uuid : %s', $accountOrUuid));
        }

        return $account;
    }
}
