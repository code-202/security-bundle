<?php

namespace Code202\Security\Service\Account;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Event\Account\CreatedEvent;
use Code202\Security\Exception;
use Code202\Security\Uuid\UuidGeneratorInterface;

class Creator
{
    private EntityManagerInterface $em;
    private UuidGeneratorInterface $uuidGenerator;
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em,
        UuidGeneratorInterface $uuidGenerator,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->uuidGenerator = $uuidGenerator;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    public function create(string $name, bool $autoFlush = true): Account
    {
        $account = new Account($this->uuidGenerator->generate(), $name);

        $violations = $this->validator->validate($account);

        if (count($violations) > 0) {
            throw new Exception\ValidationFailed($account, $violations);
        }

        $this->em->persist($account);

        $event = new CreatedEvent($account);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $account;
    }
}
