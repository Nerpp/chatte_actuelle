<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommentsVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const DELETE_COMMENT = 'DELETE_COMMENT';

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
        return in_array($attribute, [self::DELETE_COMMENT,self::EDIT])
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
                $this->authDeletComment($comment);
                break;
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                break;
        }

        return false;
    }

    private function authDeletComment($comment)
    {
        if ($this->security->isGranted('ROLE_ADMIN') && $comment->getUser() === $this->tokenUser ) {
            return true;
        }

        return false;
    }
}
