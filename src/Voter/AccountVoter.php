<?php

namespace Code202\Security\Voter;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Code202\Security\Entity\Account;
use Code202\Security\User\UserInterface;

class AccountVoter extends Voter
{
    public const LIST = 'SECURITY.ACCOUNT.LIST';
    public const SHOW = 'SECURITY.ACCOUNT.SHOW';
    public const EDIT = 'SECURITY.ACCOUNT.EDIT';
    public const ROLE = 'SECURITY.ACCOUNT.ROLE';
    public const ENABLE = 'SECURITY.ACCOUNT.ENABLE';
    public const DISABLE = 'SECURITY.ACCOUNT.DISABLE';
    public const AUTHENTICATIONS = 'SECURITY.ACCOUNT.AUTHENTICATIONS';

    protected Security $security;

    public function __construct(
        Security $security
    ) {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [
            self::LIST,
            self::SHOW,
            self::EDIT,
            self::ROLE,
            self::ENABLE,
            self::DISABLE,
            self::AUTHENTICATIONS,
        ])) {
            return false;
        }

        if (!$subject instanceof Account) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::LIST => $this->security->isGranted('ROLE_SECURITY_ACCOUNT_LIST'),
            self::SHOW => $subject == $user->getAccount() || $this->security->isGranted('ROLE_SECURITY_ACCOUNT_SHOW'),
            self::EDIT => $subject == $user->getAccount(),
            self::ROLE => $subject == $user->getAccount() || $this->security->isGranted('ROLE_SECURITY_ACCOUNT_ROLE'),
            self::ENABLE => $this->security->isGranted('ROLE_SECURITY_ACCOUNT_ENABLE'),
            self::DISABLE => $this->security->isGranted('ROLE_SECURITY_ACCOUNT_DISABLE'),
            self::AUTHENTICATIONS => $subject == $user->getAccount() || $this->security->isGranted('ROLE_SECURITY_ACCOUNT_AUTHENTICATIONS'),
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}
