<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommentsVoter extends Voter
{
    public const DELETE_COMMENT = 'DELETE_COMMENT';
    public const REPORT_COMMENT = 'REPORT_COMMENT';
    public const ALLOW_COMMENT = 'ALLOW_COMMENT';
    public const EDIT_COMMENT = 'EDIT_COMMENT';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }
    
    protected function supports(string $attribute, $comment): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::DELETE_COMMENT,self::REPORT_COMMENT,self::ALLOW_COMMENT,self::EDIT_COMMENT])
            && $comment instanceof \App\Entity\Comments;
    }

    protected function voteOnAttribute(string $attribute, $comment, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        
        if (!$user instanceof UserInterface) {
            return false;
        }
       
        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::DELETE_COMMENT:
                return $this->authDeletComment($comment);
                break;
            case self::REPORT_COMMENT:
               return $this->authReportComment($comment);
                break;
            case self::ALLOW_COMMENT:
               return $this->authAllowComment($comment);
                break;
            case self::EDIT_COMMENT:
                return $this->authEditComment($comment);
                break;
        }

        return false;
    }

    private function authDeletComment($comment)
    {
       
        if ($this->security->isGranted('ROLE_USER') && $comment->getUser() === $this->tokenUser) {
            return true;
        }

        if ($this->security->isGranted('ROLE_ADMIN') ) {
            return true;
        }

        return false;
    }

    private function authReportComment($comment)
    {

        if ($this->security->isGranted('ROLE_USER') && !$comment->isModerated()) {
            return true;
        }

        return false;
    }

    private function authAllowComment($comment)
    {
        if ($this->security->isGranted('ROLE_ADMIN') ) {
            return true;
        }

        return false;
    }

    private function authEditComment($comment)
    {
        if ($this->security->isGranted('ROLE_USER') && $comment->getUser() === $this->tokenUser) {
            return true;
        }

        return false;
    }
}
