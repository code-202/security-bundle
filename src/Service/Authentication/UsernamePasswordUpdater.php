<?php

namespace Code202\Security\Service\Authentication;

use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\PasswordChangedEvent;
use Code202\Security\Event\Authentication\UsernameChangedEvent;
use Code202\Security\Exception;
use Code202\Security\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UsernamePasswordUpdater
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected PasswordHasherFactoryInterface $hasherFactory,
        protected EventDispatcherInterface $eventDispatcher,
        protected ValidatorInterface $validator
    ) {}

    public function updatePassword(
        Authentication|string $authenticationOrUuid,
        string $newPassword,
        bool $autoFlush = true
    ): Authentication {
        if (!$newPassword) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('new_password_empty');
        }

        if ($authenticationOrUuid instanceof Authentication) {
            $authentication = $authenticationOrUuid;
        } else {
            $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
                'uuid' => $authenticationOrUuid,
            ]);
        }

        if (!$authentication instanceof Authentication) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_not_found');
        }

        if (AuthenticationType::USERNAME_PASSWORD != $authentication->getType()) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_is_not_username_password_type');
        }

        $passwordHasher = $this->hasherFactory->getPasswordHasher(User::class);

        $passwordEncoded = $passwordHasher->hash($newPassword);
        $oldPasswordEncoded = $authentication->getData('password');

        $authentication
            ->setData('password', $passwordEncoded)
            ->setVerified(false)
        ;

        $this->em->persist($authentication);

        $event = new PasswordChangedEvent($authentication, ['oldPassword' => $oldPasswordEncoded]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $authentication;
    }

    public function updateUsername(
        Authentication|string $authenticationOrUuid,
        string $newUsername,
        bool $autoFlush = true
    ): Authentication {
        if (!$newUsername) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('new_username_empty');
        }

        if ($authenticationOrUuid instanceof Authentication) {
            $authentication = $authenticationOrUuid;
        } else {
            $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
                'uuid' => $authenticationOrUuid,
            ]);
        }

        if (!$authentication instanceof Authentication) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_not_found');
        }

        if (AuthenticationType::USERNAME_PASSWORD != $authentication->getType()) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_is_not_username_password_type');
        }

        $oldUsername = $authentication->getKey();

        $authentication
            ->setKey($newUsername)
            ->setVerified(false)
        ;

        $violations = $this->validator->validate($authentication);

        if (count($violations) > 0) {
            throw new Exception\ValidationFailed($authentication, $violations);
        }

        $this->em->persist($authentication);

        $event = new UsernameChangedEvent($authentication, ['oldUsername' => $oldUsername, 'username' => $newUsername]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $authentication;
    }
}
