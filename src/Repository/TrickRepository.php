<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    // // /**
    // //  * @return Trick[] Returns an array of Trick objects
    // //  */
    // /*
    public function loadXtricks($firstValue, $maxValue)
    {
        return $this->createQueryBuilder('t')
            // ->select("t.id, t.cover")
            ->orderBy('t.id', 'DESC')
            ->setMaxResults($maxValue)
            ->setFirstResult($firstValue)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function countTricks()
    {
        return $this->createQueryBuilder('t')
            ->select("count(t.id) as count")
            ->getQuery()
            ->getArrayResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
