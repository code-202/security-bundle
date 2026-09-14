<?php

namespace Code202\Security\Service\Authentication;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\CreatedEvent;
use Code202\Security\Exception;
use Code202\Security\User\User;
use Code202\Security\Uuid\UuidGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UsernamePasswordCreator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly ValidatorInterface $validator,
    ) {}

    public function create(Account|string $accountOrUuid, string $username, ?string $password = null, bool $autoFlush = true): Authentication
    {
        if ($accountOrUuid instanceof Account) {
            $account = $accountOrUuid;
        } else {
            $account = $this->em->getRepository(Account::class)->findOneBy(['uuid' => $accountOrUuid]);
        }

        if (!$account instanceof Account) {
            throw new Exception\AuthenticationUsernamePasswordCreator(sprintf('Account not found for uuid : %s', $accountOrUuid));
        }

        $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
            'account' => $account,
            'type' => AuthenticationType::USERNAME_PASSWORD,
        ]);

        if ($authentication instanceof Authentication) {
            throw new Exception\AuthenticationUsernamePasswordCreator(sprintf('This account has already got an username/password authentication mode'));
        }

        $authentication = new Authentication($this->uuidGenerator->generate(), AuthenticationType::USERNAME_PASSWORD, $account);
        $authentication->setKey($username);

        $violations = $this->validator->validate($authentication);

        if (count($violations) > 0) {
            throw new Exception\ValidationFailed($authentication, $violations);
        }

        if ($password) {
            $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);
            $authentication->setData('password', $passwordHasher->hash($password));
        }

        $this->em->persist($authentication);

        $event = new CreatedEvent($authentication, ['username' => $username]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $authentication;
    }
}
