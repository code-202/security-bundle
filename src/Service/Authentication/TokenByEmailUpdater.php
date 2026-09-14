<?php

namespace Code202\Security\Service\Authentication;

use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\EmailChangedEvent;
use Code202\Security\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TokenByEmailUpdater
{
    protected EntityManagerInterface $em;
    protected EventDispatcherInterface $eventDispatcher;
    protected ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    public function updateEmail(Authentication|string $authenticationOrUuid, string $newEmail, bool $autoFlush = true): void
    {
        if ($authenticationOrUuid instanceof Authentication) {
            $authentication = $authenticationOrUuid;
        } else {
            $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
                'uuid' => $authenticationOrUuid,
            ]);
        }

        if (!$authentication || !$authentication->isEnabled()) {
            throw new Exception\AuthenticationTokenByEmailUpdater('authentication_not_found');
        }

        if (AuthenticationType::TOKEN_BY_EMAIL != $authentication->getType()) {
            throw new Exception\AuthenticationTokenByEmailUpdater('authentication_is_not_token_by_email_type');
        }

        if (!$newEmail) {
            throw new Exception\AuthenticationTokenByEmailUpdater('email_empty');
        }

        if ($newEmail == $authentication->getKey()) {
            throw new Exception\AuthenticationTokenByEmailUpdater('email_identical');
        }

        $oldEmail = $authentication->getKey();

        $authentication
            ->setKey($newEmail)
            ->setVerified(false)
            ->setDatas([])
        ;

        $violations = $this->validator->validate($authentication);

        if (count($violations) > 0) {
            throw new Exception\ValidationFailed($authentication, $violations);
        }

        $this->em->persist($authentication);

        $event = new EmailChangedEvent($authentication, ['email' => $authentication->getKey(), 'oldEmail' => $oldEmail]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }
}
