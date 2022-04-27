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
    public const VIEW_ALL_DRAFT = 'VIEW_ALL_DRAFT';
    public const VIEW_PERSONNAL_DRAFT = 'VIEW_PERSONNAL_ARTICLE';
    

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::SECTION_ADMIN,self::NEW_ARTICLE,self::VIEW_ALL_DRAFT,self::VIEW_PERSONNAL_DRAFT])
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

        switch ($attribute) {
            case self::SECTION_ADMIN:
                return $this->$this->authSectionAdmin();
                break;
            case self::NEW_ARTICLE:
                return $this->$this->authNewArticle();
                break;
            case self::VIEW_ALL_DRAFT:
                return $this->authViewAllDraft();
                break;
            case self::VIEW_PERSONNAL_DRAFT:
                return $this->authPersonnalDraft();
                break;
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

    private function authNewArticle()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    private function authViewAllDraft()
    {
        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }

        return false;
    }

    private function authPersonnalDraft()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }
    
}
