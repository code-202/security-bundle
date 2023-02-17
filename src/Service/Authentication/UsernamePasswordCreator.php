<?php

namespace Code202\Security\Service\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\CreatedEvent;
use Code202\Security\Exception;
use Code202\Security\User\User;
use Code202\Security\Uuid\UuidGeneratorInterface;

class UsernamePasswordCreator
{
    private EntityManagerInterface $em;
    private UuidGeneratorInterface $uuidGenerator;
    private EventDispatcherInterface $eventDispatcher;
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    public function __construct(
        EntityManagerInterface $em,
        UuidGeneratorInterface $uuidGenerator,
        EventDispatcherInterface $eventDispatcher,
        PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
        $this->em = $em;
        $this->uuidGenerator = $uuidGenerator;
        $this->eventDispatcher = $eventDispatcher;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    public function create(Account|string $accountOrUuid, string $username, string $password = null, bool $autoFlush = true): Authentication
    {
        if ($accountOrUuid instanceof Account) {
            $account = $accountOrUuid;
        } else {
            $account = $this->em->getRepository(Account::class)->findOneBy([ 'uuid' => $accountOrUuid ]);
        }

        if (!$account) {
            throw new Exception\AuthenticationUsernamePasswordCreator(sprintf('Account not found for uuid : %s', $accountOrUuid));
        }

        $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
            'account' => $account,
            'type' => AuthenticationType::USERNAME_PASSWORD
        ]);

        if ($authentication) {
            throw new Exception\AuthenticationUsernamePasswordCreator(sprintf('This account has already got an username/password authentication mode'));
        }

        $authentication = new Authentication($this->uuidGenerator->generate(), AuthenticationType::USERNAME_PASSWORD, $account);
        $authentication->setKey($username);

        if ($password) {
            $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);
            $authentication->setData('password', $passwordHasher->hash($password));
        }

        $this->em->persist($authentication);

        $event = new CreatedEvent($authentication);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $authentication;
    }
}
