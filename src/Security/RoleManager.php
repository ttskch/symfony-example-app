<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class RoleManager
{
    private array $roles;
    private Security $security;
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(Security $security, RoleHierarchyInterface $roleHierarchy)
    {
        $this->roles = [
            'ROLE_ALLOWED_TO_ADMIN',
            'ROLE_ALLOWED_TO_EDIT_USER',
            'ROLE_ALLOWED_TO_EDIT',
            'ROLE_ALLOWED_TO_VIEW',
        ];

        $this->security = $security;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function getReachableRoles(UserInterface $user = null): array
    {
        $user = $user ?? $this->security->getUser();

        return array_filter($this->roles, fn(string $role) => $this->isGranted($user, $role));
    }

    public function isGranted(UserInterface $user, string $targetRole): bool
    {
        return in_array($targetRole, $this->roleHierarchy->getReachableRoleNames($user->getRoles()));
    }
}
