<?php

namespace Code202\Security\Service\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\PasswordChangedEvent;
use Code202\Security\Event\Authentication\UsernameChangedEvent;
use Code202\Security\Exception;
use Code202\Security\User\User;

class UsernamePasswordUpdater
{
    protected $em;
    protected $hasherFactory;
    protected $eventDispatcher;
    protected $validator;

    public function __construct(
        EntityManagerInterface $em,
        PasswordHasherFactoryInterface $hasherFactory,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->hasherFactory = $hasherFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    public function updatePassword(
        string|Authentication $authenticationOrUuid,
        string $newPassword,
        bool $verifyOldPassword = false,
        string $oldPassword = '',
        bool $autoFlush = true
    ): void {
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

        if (!$authentication) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_not_found');
        }

        if ($authentication->getType() != AuthenticationType::USERNAME_PASSWORD) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_is_not_username_password_type');
        }

        $passwordHasher = $this->hasherFactory->getPasswordHasher(User::class);

        if ($verifyOldPassword && !$oldPassword) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('old_password_empty');
        }

        if ($verifyOldPassword && !$passwordHasher->verify($authentication->getData('password'), $oldPassword)) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('old_password_not_match');
        }

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
    }

    public function updateUsername(
        Authentication|string $authenticationOrUuid,
        string $newUsername,
        bool $verifyPassword = false,
        string $password = '',
        bool $autoFlush = true
    ) {
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

        if (!$authentication) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_not_found');
        }

        if ($authentication->getType() != AuthenticationType::USERNAME_PASSWORD) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('authentication_is_not_username_password_type');
        }

        $passwordHasher = $this->hasherFactory->getPasswordHasher(User::class);

        if ($verifyPassword && !$password) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('password_empty');
        }

        if ($verifyPassword && !$passwordHasher->verify($authentication->getData('password'), $password)) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('password_not_match');
        }

        $oldUsername = $authentication->getKey();

        $authentication
            ->setKey($newUsername)
            ->setVerified(false)
        ;

        $violations = $this->validator->validate($authentication);

        if (count($violations) > 0) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('username_already_used');
        }

        $this->em->persist($authentication);

        $event = new UsernameChangedEvent($authentication, ['oldUsername' => $oldUsername]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }
}
