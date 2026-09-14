<?php

declare(strict_types=1);

namespace Code202\Security\Service\Account;

use Code202\Security\Entity\Account;
use Code202\Security\Event\Account\CreatedEvent;
use Code202\Security\Exception;
use Code202\Security\Uuid\UuidGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Creator
{
    private readonly EntityManagerInterface $em;
    private readonly UuidGeneratorInterface $uuidGenerator;
    private readonly EventDispatcherInterface $eventDispatcher;
    private readonly ValidatorInterface $validator;

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

        $event = new CreatedEvent($account, ['name' => $name]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $account;
    }
}
