<?php

declare(strict_types=1);

namespace App\Pagination\Paginator;

use App\Entity\Customer;
use App\Entity\Project;
use App\Entity\User;
use App\Pagination\Criteria\ProjectCriteria;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ttskch\PaginatorBundle\Doctrine\Counter;
use Ttskch\PaginatorBundle\Doctrine\Slicer;

class ProjectPaginator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function sliceByCriteria(ProjectCriteria $criteria): \ArrayIterator
    {
        $qb = $this->createQueryBuilderFromCriteria($criteria);
        $slicer = new Slicer($qb);

        return $slicer($criteria, true);
    }

    public function countByCriteria(ProjectCriteria $criteria): int
    {
        $qb = $this->createQueryBuilderFromCriteria($criteria);
        $counter = new Counter($qb);

        return $counter($criteria);
    }

    public function createQueryBuilderFromCriteria(ProjectCriteria $criteria): QueryBuilder
    {
        $expr = $this->em->getExpressionBuilder();

        /** @var ProjectRepository $repository */
        $repository = $this->em->getRepository(Project::class);

        $qb = $repository->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->leftJoin('p.user', 'u')
        ;

        if ($criteria->query !== null) {
            $qb
                ->andWhere($expr->orX(
                    'p.name like :query',
                    'p.note like :query',
                ))
                ->setParameter('query', '%'.str_replace('%', '\%', $criteria->query).'%')
            ;
        }

        if ($criteria->states) {
            $qb->andWhere($expr->in('p.state', $criteria->states));
        }

        if ($criteria->customers) {
            $customerIds = $criteria->customers->map(fn(Customer $customer) => $customer->getId())->toArray();
            $qb->andWhere($expr->in('c', $customerIds));
        }

        if ($criteria->users) {
            $userIds = $criteria->users->map(fn(User $user) => $user->getId())->toArray();
            $qb->andWhere($expr->in('u', $userIds));
        }

        return $qb;
    }
}
