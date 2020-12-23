<?php

declare(strict_types=1);

namespace App\Pagination\Criteria;

use Doctrine\Common\Collections\Collection;
use Ttskch\PaginatorBundle\Entity\Criteria;

class ProjectCriteria extends Criteria
{
    public ?string $query = null;
    public ?array $states = null;
    public ?Collection $customers = null;
    public ?Collection $users = null;
}
