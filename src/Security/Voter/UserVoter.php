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
    public const CREATE_TAG = 'CREATE_TAG';
    public const INDEX_TAGS = 'INDEX_TAGS';
    public const ACCES_CENSURE = 'ACCES_CENSURE';
    public const EDITO_EDIT ='EDITO_EDIT';
    public const SEND_COMMENTS = 'SEND_COMMENTS';
    

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::SECTION_ADMIN,self::NEW_ARTICLE,self::VIEW_ALL_DRAFT,self::VIEW_PERSONNAL_DRAFT,self::CREATE_TAG,self::INDEX_TAGS,self::ACCES_CENSURE,self::EDITO_EDIT,self::SEND_COMMENTS])
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
                return $this->authSectionAdmin();
                break;
            case self::NEW_ARTICLE:
                return $this->authNewArticle();
                break;
            case self::VIEW_ALL_DRAFT:
                return $this->authViewAllDraft();
                break;
            case self::VIEW_PERSONNAL_DRAFT:
                return $this->authPersonnalDraft();
                break;
            case self::CREATE_TAG:
               return $this->authCreateTag();
                break;
            case self::INDEX_TAGS:
                return $this->authIndexTag();
                break;
            case self::ACCES_CENSURE:
                return $this->authAccessCensure();
                break;
            case self::EDITO_EDIT:
                return $this->authEditEdito();
                break;
            case self::SEND_COMMENTS:
                return $this->authSendComments();
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

    private function authCreateTag()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    private function authIndexTag()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    private function authAccessCensure()
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }
       
        foreach ($this->security->getUser()->getArticles() as $articlesChecked) {
            if ($articlesChecked->getCensure()) {
               return true;
            }
        }

        return false;
    }

    private function authEditEdito()
    {
        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }

        return false;
    }

    private function authSendComments()
    {
        if ($this->security->isGranted('ROLE_USER')) {
            return true;
        }

        return false;
    }
    
}
