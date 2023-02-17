<?php

namespace Code202\Security\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Entity\Session;
use Code202\Security\Event\User\RefreshedEvent;
use Code202\Security\Uuid\UuidGeneratorInterface;
use Code202\Security\Uuid\UuidValidatorInterface;

class Provider implements UserProviderInterface
{
    protected EntityManagerInterface $em;
    protected UuidGeneratorInterface $uuidGenerator;
    protected UuidValidatorInterface $uuidValidator;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        UuidGeneratorInterface $uuidGenerator,
        UuidValidatorInterface $uuidValidator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->uuidGenerator = $uuidGenerator;
        $this->uuidValidator = $uuidValidator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     * @throws UserNotFoundException    if the user is not found
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $user = $this->loadUserByIdentifier($user->getUserIdentifier());

        $event = new RefreshedEvent($user);
        $this->eventDispatcher->dispatch($event);

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    /**
     * Loads the user for the given user identifier (e.g. username or email).
     *
     * This method must throw UserNotFoundException if the user is not found.
     *
     * @throws UserNotFoundException
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (preg_match('/^(.*):(.*)$/', $identifier, $matches)) {
            return $this->loadUserByTypeAndKey($matches[1], $matches[2]);
        }

        if ($this->uuidValidator->validate($identifier)) {
            return $this->loadUserByUuuid($identifier);
        }

        return $this->loadUserByTypeAndKey(AuthenticationType::USERNAME_PASSWORD->value, $identifier);
    }

    protected function loadUserByUuuid(string $uuid): UserInterface
    {
        $qb = $this->em->getRepository(Session::class)->createQueryBuilder('s')
            ->addSelect('a')
            ->addSelect('c')
            ->innerJoin('s.authentication', 'a')
            ->innerJoin('a.account', 'c')
            ->andWhere('s.uuid = :uuid')
            ->setParameter('uuid', $uuid)
        ;

        $session = $qb->getQuery()->getOneOrNullResult();

        if (!$session) {
            throw new UserNotFoundException();
        }

        return new User($session);
    }

    protected function loadUserByTypeAndKey(string $type, string $key): UserInterface
    {
        $qb = $this->em->getRepository(Authentication::class)->createQueryBuilder('a')
            ->addSelect('c')
            ->innerJoin('a.account', 'c')
            ->andWhere('a.type = :type')
            ->setParameter('type', $type)
            ->andWhere('a.key = :key')
            ->setParameter('key', $key)
        ;

        $authentication = $qb->getQuery()->getOneOrNullResult();

        if (!$authentication) {
            throw new UserNotFoundException();
        }

        $session = new Session($this->uuidGenerator->generate(), $authentication);

        return new User($session);
    }
}
