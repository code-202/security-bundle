<?php

declare(strict_types=1);

namespace Code202\Security\Service\Session;

use Code202\Security\Entity\Session;
use Code202\Security\Event\Session\DeletedEvent;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Deleter
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected EventDispatcherInterface $eventDispatcher
    ) {}

    public function delete(Session $session, bool $autoFlush = true): void
    {
        $now = new DateTime();
        if ($session->getExpiredAt() instanceof DateTimeInterface && $session->getExpiredAt() < $now) {
            return;
        }

        $session->setExpiredAt($now);

        $this->em->persist($session);

        $event = new DeletedEvent($session);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }
}
