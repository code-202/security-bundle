<?php

declare(strict_types=1);

namespace Code202\Security\Service\Session;

use Code202\Security\Entity\Session;
use Code202\Security\Event\Session\TrustEvent;
use Code202\Security\Event\Session\UntrustEvent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Truster
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly int $trustDuration
    ) {}

    public function trust(Session $session, bool $autoFlush = true): void
    {
        $now = new DateTime();

        $session->setTrustUntil($now->modify('+' . $this->trustDuration . ' seconds'));

        $this->em->persist($session);

        $event = new TrustEvent($session);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }

    public function untrust(Session $session, bool $autoFlush = true): void
    {
        $now = new DateTime();

        $session->setTrustUntil($now);

        $this->em->persist($session);

        $event = new UntrustEvent($session);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }
}
