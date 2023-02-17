<?php

namespace Code202\Security\Service\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\CreatedEvent;
use Code202\Security\Exception;
use Code202\Security\User\User;
use Code202\Security\Uuid\UuidGeneratorInterface;

class TokenByEmailCreator
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

    public function createEmail(Account|string $accountOrUuid, string $newEmail, bool $autoFlush = true)
    {
        if ($accountOrUuid instanceof Account) {
            $account = $accountOrUuid;
        } else {
            $account = $this->em->getRepository(Account::class)->findOneBy([ 'uuid' => $accountOrUuid ]);
        }

        if (!$account || !$account->isEnabled()) {
            throw new Exception\AuthenticationTokenByEmailCreator(sprintf('Account not found for uuid : %s', $accountOrUuid));
        }

        if (!$newEmail) {
            throw new Exception\AuthenticationTokenByEmailCreator('email_empty');
        }

        $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
            'account' => $account,
            'type' => AuthenticationType::TOKEN_BY_EMAIL
        ]);

        if ($authentication) {
            throw new Exception\AuthenticationTokenByEmailCreator(sprintf('This account has already got an email/token authentication mode'));
        }

        $authentication = new Authentication($this->uuidGenerator->generate(), AuthenticationType::TOKEN_BY_EMAIL, $account);
        $authentication->setKey($newEmail);

        $violations = $this->validator->validate($authentication);

        if (count($violations) > 0) {
            throw new Exception\AuthenticationTokenByEmailCreator('Impossible to create an email/token authentication mode with this email for this account');
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
