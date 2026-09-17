<?php

namespace Code202\Security\Service\Activity\Trigger;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Activity\Trigger;
use Code202\Security\Entity\Activity\TriggerReference;
use Code202\Security\Entity\Activity\TriggerSession;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\Session;
use Code202\Security\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionProvider implements ProviderInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Security $security,
        protected RequestStack $requestStack
    ) {}

    public function supports(): bool
    {
        $user = $this->security->getUser();

        return $user instanceof UserInterface;
    }

    public function get(): Trigger
    {
        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            throw new RuntimeException('user is not an instance of UserInterface');
        }

        $session = $user->getSession();

        $repository = $this->em->getRepository(TriggerSession::class);

        $res = $repository->findOneBy([
            'reference' => $session,
        ]);

        if (!$res instanceof TriggerSession) {
            $res = new TriggerSession($session);
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request && $request->headers->has('user-agent')) {
            $res->setData('user_agent', $request->headers->get('user-agent') ?? 'unknown');
        }

        return $res;
    }

    /** @return TriggerSession[] */
    public function findAll(TriggerReference $reference): array
    {
        $repository = $this->em->getRepository(TriggerSession::class);

        $qb = $repository->createQueryBuilder('ts')
            ->setParameter('reference', $reference)
        ;

        if ($reference instanceof Session) {
            $qb = $repository->createQueryBuilder('ts')
                ->andWhere('ts.reference = :reference')
                ->setParameter('reference', $reference)
            ;

            return $qb->getQuery()->getResult();
        }

        if ($reference instanceof Authentication) {
            $qb = $repository->createQueryBuilder('ts')
                ->innerJoin('ts.reference', 's')
                ->andWhere('s.authentication = :reference')
                ->setParameter('reference', $reference)
            ;

            return $qb->getQuery()->getResult();
        }

        if ($reference instanceof Account) {
            $qb = $repository->createQueryBuilder('ts')
                ->innerJoin('ts.reference', 's')
                ->innerJoin('s.authentication', 'a')
                ->andWhere('a.account = :reference')
                ->setParameter('reference', $reference)
            ;

            return $qb->getQuery()->getResult();
        }

        return [];
    }
}
