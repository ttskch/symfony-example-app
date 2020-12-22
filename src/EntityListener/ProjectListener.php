<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Project;
use App\Service\Namer;
use Doctrine\ORM\Event\PreFlushEventArgs;

class ProjectListener
{
    private Namer $namer;

    public function __construct(Namer $namer)
    {
        $this->namer = $namer;
    }

    public function preFlush(Project $project, PreFlushEventArgs $event)
    {
        $project->name = $this->namer->beautify($project->name);
    }
}
