<?php

namespace App\Repository;

use App\Entity\ProductoPedido;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductoPedido|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductoPedido|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductoPedido[]    findAll()
 * @method ProductoPedido[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductoPedidoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductoPedido::class);
    }

    // /**
    //  * @return ProductoPedido[] Returns an array of ProductoPedido objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductoPedido
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
