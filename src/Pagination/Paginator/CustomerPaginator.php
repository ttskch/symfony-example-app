<?php

declare(strict_types=1);

namespace App\Pagination\Paginator;

use App\Entity\Customer;
use App\Pagination\Criteria\CustomerCriteria;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ttskch\PaginatorBundle\Doctrine\Counter;
use Ttskch\PaginatorBundle\Doctrine\Slicer;

class CustomerPaginator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function sliceByCriteria(CustomerCriteria $criteria): \ArrayIterator
    {
        $qb = $this->createQueryBuilderFromCriteria($criteria);
        $slicer = new Slicer($qb);

        return $slicer($criteria, true);
    }

    public function countByCriteria(CustomerCriteria $criteria): int
    {
        $qb = $this->createQueryBuilderFromCriteria($criteria);
        $counter = new Counter($qb);

        return $counter($criteria);
    }

    public function createQueryBuilderFromCriteria(CustomerCriteria $criteria): QueryBuilder
    {
        $expr = $this->em->getExpressionBuilder();

        /** @var CustomerRepository $repository */
        $repository = $this->em->getRepository(Customer::class);

        $qb = $repository->createQueryBuilder('c')
            ->leftJoin('c.people', 'p')
        ;

        if ($criteria->query !== null) {
            $qb
                ->andWhere($expr->orX(
                    'c.name like :query',
                    'p.fullName like :query',
                    'p.email like :query',
                    'p.tel like :query',
                    'p.note like :query',
                ))
                ->setParameter('query', '%'.str_replace('%', '\%', $criteria->query).'%')
            ;
        }

        if ($criteria->states) {
            $qb->andWhere($expr->in('c.state', $criteria->states));
        }

        return $qb;
    }
}
