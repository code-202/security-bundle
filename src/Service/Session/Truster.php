<?php

namespace Code202\Security\Service\Session;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Session;
use Code202\Security\Event\Session\TrustEvent;
use Code202\Security\Event\Session\UntrustEvent;

class Truster
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $eventDispatcher,
        private int $trustDuration
    ) {
    }

    public function trust(Session $session, bool $autoFlush = true)
    {
        $now = new \Datetime();

        $session->setTrustUntil($now->modify('+'.$this->trustDuration.' seconds'));

        $this->em->persist($session);

        $event = new TrustEvent($session);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }

    public function untrust(Session $session, bool $autoFlush = true)
    {
        $now = new \Datetime();

        $session->setTrustUntil($now);

        $this->em->persist($session);

        $event = new UntrustEvent($session);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }
    }
}
