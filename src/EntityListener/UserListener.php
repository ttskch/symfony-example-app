<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserListener
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function postLoad(User $user, LifecycleEventArgs $event)
    {
        $user->displayName = $user->displayName ?? $user->email;
    }

    public function preFlush(User $user, PreFlushEventArgs $event)
    {
        if ($user->plainPassword) {
            $user->password = $this->encoder->encodePassword($user, $user->plainPassword);
        }
    }
}
