<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Customer;
use App\Service\Namer;
use Doctrine\ORM\Event\PreFlushEventArgs;

class CustomerListener
{
    private Namer $namer;

    public function __construct(Namer $namer)
    {
        $this->namer = $namer;
    }

    public function preFlush(Customer $customer, PreFlushEventArgs $event)
    {
        $customer->name = $this->namer->beautify($customer->name);
    }
}
