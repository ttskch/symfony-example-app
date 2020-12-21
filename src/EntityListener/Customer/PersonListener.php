<?php

declare(strict_types=1);

namespace App\EntityListener\Customer;

use App\Entity\Customer\Person;
use App\Service\Namer;
use Doctrine\ORM\Event\PreFlushEventArgs;

class PersonListener
{
    private Namer $namer;

    public function __construct(Namer $namer)
    {
        $this->namer = $namer;
    }

    public function preFlush(Person $person, PreFlushEventArgs $event)
    {
        $person->fullName = $this->namer->beautify($person->fullName);
    }
}
