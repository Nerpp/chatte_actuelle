<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ImagesVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';
    public const DELETE_IMAGE = 'DELETE_IMAGE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }

    protected function supports(string $attribute, $image): bool
    {
        
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW,self::DELETE_IMAGE])
            && $image instanceof \App\Entity\Images;
    }

    protected function voteOnAttribute(string $attribute, $image, TokenInterface $token): bool
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
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;
            case self::DELETE_IMAGE:
                dd('test');
                return $this->authDeleteImage($image);
                break;
        }

        return false;
    }

    private function authDeleteImage($image)
    {
        if ($this->security->isGranted('ROLE_ADMIN') && $image->getUser() === $this->tokenUser ) {
            return true;
        }

        return false;
    }
}
