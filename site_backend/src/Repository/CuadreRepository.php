<?php

namespace App\Repository;

use App\Entity\Cuadre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cuadre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cuadre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cuadre[]    findAll()
 * @method Cuadre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CuadreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cuadre::class);
    }

    // /**
    //  * @return Cuadre[] Returns an array of Cuadre objects
    //  */

    public function findByNegocioFecha($negocio)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.negocio = :val')
            ->setParameter('val', $negocio)
            ->orderBy('c.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByNegocioFecha2($negocio)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.negocio = :val')
            ->setParameter('val', $negocio)
            ->orderBy('c.fecha', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }


    /*
    public function findOneBySomeField($value): ?Cuadre
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
