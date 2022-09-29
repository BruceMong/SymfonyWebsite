<?php

namespace App\Repository;

use App\Entity\DataHTML;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DataHTML|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataHTML|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataHTML[]    findAll()
 * @method DataHTML[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataHTMLRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataHTML::class);
    }

    // /**
    //  * @return DataHTML[] Returns an array of DataHTML objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DataHTML
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
