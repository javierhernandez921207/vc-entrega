<?php

namespace App\Repository;

use App\Entity\Transporte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transporte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transporte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transporte[]    findAll()
 * @method Transporte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransporteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transporte::class);
    }

    // /**
    //  * @return Transporte[] Returns an array of Transporte objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transporte
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
