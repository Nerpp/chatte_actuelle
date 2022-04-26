<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

use function PHPUnit\Framework\matches;

class UserVoter extends Voter
{
    public const NEW_ARTICLE = 'NEW_ARTICLE';
    public const SECTION_ADMIN = 'SECTION_ADMIN';
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::SECTION_ADMIN,self::NEW_ARTICLE,self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }

        match($attribute){
            self::SECTION_ADMIN => $this->authSectionAdmin(),
            self::NEW_ARTICLE => $this->authNewArticle(),
            default => false
        };
        
    }

    private function authNewArticle()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    private function authSectionAdmin()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }
}
