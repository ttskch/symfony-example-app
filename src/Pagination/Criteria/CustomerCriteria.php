<?php

declare(strict_types=1);

namespace App\Pagination\Criteria;

use Ttskch\PaginatorBundle\Entity\Criteria;

class CustomerCriteria extends Criteria
{
    public ?string $query = null;
    public ?array $states = null;
}
