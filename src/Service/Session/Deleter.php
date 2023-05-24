<?php

namespace Code202\Security\Service\Session;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Session;
use Code202\Security\Event\Session\DeletedEvent;

class Deleter
{
    protected EntityManagerInterface $em;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function delete(Session $session, bool $autoFlush = true)
    {
        $now = new \Datetime();
        if ($session->getExpiredAt() !== null && $session->getExpiredAt() < $now) {
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
