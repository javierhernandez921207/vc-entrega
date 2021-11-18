<?php

namespace App\Repository;

use App\Entity\TransportePedido;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TransportePedido|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransportePedido|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransportePedido[]    findAll()
 * @method TransportePedido[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransportePedidoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransportePedido::class);
    }

    // /**
    //  * @return TransportePedido[] Returns an array of TransportePedido objects
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
    public function findOneBySomeField($value): ?TransportePedido
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
