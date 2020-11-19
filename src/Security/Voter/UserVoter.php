<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\RoleManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const EDIT = 'EDIT';

    private RoleManager $rm;

    public function __construct(RoleManager $rm)
    {
        $this->rm = $rm;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
        }

        throw new \LogicException();
    }

    private function canEdit(User $them, User $me): bool
    {
        if (!$this->rm->isGranted($me, 'ROLE_ALLOWED_TO_EDIT_USER')) {
            return false;
        }

        // only admin can edit admin
        if ($this->rm->isGranted($them, 'ROLE_ALLOWED_TO_ADMIN')) {
            if (!$this->rm->isGranted($me, 'ROLE_ALLOWED_TO_ADMIN')) {
                return false;
            }
        }

        return true;
    }
}
