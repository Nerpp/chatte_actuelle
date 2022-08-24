<?php

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        if ($user->getWarning() >= 3) {
            // the message passed to this exception is meant to be displayed to the user
            // @codeCoverageIgnoreStart
            throw new CustomUserMessageAccountStatusException('Votre compte à été suspendu veuillez contacter un administrateur.');
            // @codeCoverageIgnoreEnd
        }

        if (!$user->isVerified()) {
            // the message passed to this exception is meant to be displayed to the user
            // @codeCoverageIgnoreStart
            throw new CustomUserMessageAccountStatusException('Votre compte n\'a pas été verifié.');
            // @codeCoverageIgnoreEnd
        }
    }
}
