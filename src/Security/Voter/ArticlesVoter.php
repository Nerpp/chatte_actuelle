<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ArticlesVoter extends Voter
{
    public const VIEW_DRAFT = 'VIEW_DRAFT';
    public const EDIT_ARTICLE = 'EDIT_ARTICLE';
    public const DELETE_ARTICLE = 'DELETE_ARTICLE';
    public const CENSURE_ARTICLE = 'CENSURE_ARTICLE';
    public const VIEW_CENSURE = 'VIEW_CENSURE';
    
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }


    protected function supports(string $attribute, $article): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW_DRAFT,self::EDIT_ARTICLE,self::DELETE_ARTICLE,self::CENSURE_ARTICLE,self::VIEW_CENSURE])
            && $article instanceof \App\Entity\Articles;
    }

    protected function voteOnAttribute(string $attribute, $article, TokenInterface $token): bool
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
            case self::VIEW_DRAFT:
                return $this->authViewDraft($article);
                break;
            case self::EDIT_ARTICLE:
                return $this->authEditArticle($article);
                break;
            case self::DELETE_ARTICLE:
                return $this->authDeleteArticle($article);
                break;
            case self::CENSURE_ARTICLE:
                return $this->authCensureArticle($article);
                break;
            case self::VIEW_CENSURE:
               return $this->authViewCensure($article);
                break;
        }

        return false;

    }

    private function authViewDraft($article)
    {
        if ($this->security->isGranted('ROLE_ADMIN') && $article->getUser() === $this->tokenUser ) {
            return true;
        }

        return false;
    }

    private function authEditArticle($article)
    {
        if ($this->security->isGranted('ROLE_ADMIN') && $article->getUser() === $this->tokenUser ) {
            return true;
        }

        return false;
    }

    private function authDeleteArticle($article)
    {
        if ($this->security->isGranted('ROLE_ADMIN') && $article->getUser() === $this->tokenUser ) {
            return true;
        }

        return false;
    }

    private function authCensureArticle($article)
    {
        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }

        return false;
    }

    private function authViewCensure($article)
    {
        if ($this->security->isGranted('ROLE_ADMIN') && $article->getUser() === $this->tokenUser ) {
            return true;
        }

        return false;
    }
}
