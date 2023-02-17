<?php

namespace Code202\Security\Service\Account;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Event\Account\NameChangedEvent;
use Code202\Security\Exception;

class Updater
{
    protected $em;
    protected $eventDispatcher;
    protected $validator;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    public function updateName(
        Account|string $accountOrUuid,
        string $newName,
        bool $autoFlush = true
    ) {
        if (!$newName) {
            throw new Exception\AccountUpdater('new_name_empty');
        }

        if ($accountOrUuid instanceof Account) {
            $account = $accountOrUuid;
        } else {
            $account = $this->em->getRepository(Account::class)->findOneBy([
                'uuid' => $accountOrUuid,
            ]);
        }

        if (!$account) {
            throw new Exception\AccountUpdater('account_not_found');
        }

        $oldName = $account->getName();

        $account
            ->setName($newName)
        ;

        $violations = $this->validator->validate($account);

        if (count($violations) > 0) {
            throw new Exception\AccountUpdater('name_already_used');
        }

        $this->em->persist($account);

        $event = new NameChangedEvent($account, ['oldName' => $oldName]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }
}
