<?php

namespace Code202\Security\Service\Account;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Event\Account\GrantedEvent;
use Code202\Security\Event\Account\RevokedEvent;
use Code202\Security\Exception;

class RoleManipulator
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

    public function grant(Account|string $accountOrUuid, string $role, bool $autoFlush = true)
    {
        $account = $this->getAccount($accountOrUuid);

        $roles = $account->getRoles();

        if (in_array($role, $roles)) {
            return;
        }

        $roles[] = $role;

        $account->setRoles(array_values($roles));

        $this->em->persist($account);

        $event = new GrantedEvent($account, ['role' => $role]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }

    public function revoke(Account|string $accountOrUuid, string $role, bool $autoFlush = true)
    {
        $account = $this->getAccount($accountOrUuid);

        $roles = $account->getRoles();

        $key = array_search($role, $roles);

        if (false === $key) {
            return;
        }

        unset($roles[$key]);

        $account->setRoles(array_values($roles));

        $this->em->persist($account);

        $event = new RevokedEvent($account, ['role' => $role]);
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
