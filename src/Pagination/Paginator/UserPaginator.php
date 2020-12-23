<?php

declare(strict_types=1);

namespace App\Pagination\Paginator;

use App\Entity\User;
use App\Pagination\Criteria\UserCriteria;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ttskch\PaginatorBundle\Doctrine\Counter;
use Ttskch\PaginatorBundle\Doctrine\Slicer;

class UserPaginator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function sliceByCriteria(UserCriteria $criteria): \ArrayIterator
    {
        $qb = $this->createQueryBuilderFromCriteria($criteria);
        $slicer = new Slicer($qb);

        return $slicer($criteria, true);
    }

    public function countByCriteria(UserCriteria $criteria): int
    {
        $qb = $this->createQueryBuilderFromCriteria($criteria);
        $counter = new Counter($qb);

        return $counter($criteria);
    }

    public function createQueryBuilderFromCriteria(UserCriteria $criteria): QueryBuilder
    {
        $expr = $this->em->getExpressionBuilder();

        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);

        $qb = $repository->createQueryBuilder('u');

        if ($criteria->query !== null) {
            $qb
                ->andWhere($expr->orX(
                    'u.email like :query',
                    'u.displayName like :query',
                ))
                ->setParameter('query', '%'.str_replace('%', '\%', $criteria->query).'%')
            ;
        }

        return $qb;
    }
}
