<?php

declare(strict_types=1);

namespace App\Pagination\Criteria;

use Ttskch\PaginatorBundle\Entity\Criteria;

class UserCriteria extends Criteria
{
    public ?string $query = null;
}
