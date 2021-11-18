<?php

namespace App\Repository;

use App\Entity\Pedido;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pedido|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pedido|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pedido[]    findAll()
 * @method Pedido[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PedidoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pedido::class);
    }

    public function findPedidoRecientes()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function findPedidoConfeccion($cliente): ?Pedido
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cliente = :cli')
            ->andWhere('p.estado = :est')
            ->setParameter('cli', $cliente)
            ->setParameter('est', "confección")
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findAllByCliente($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cliente = :val')
            ->setParameter('val', $value)
            ->orderBy('p.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllPendientes()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado =:est ')
            ->setParameter('est', 'pendiente')
            ->getQuery()
            ->getResult();
    }

    public function findAllCompletados()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado =:est ')
            ->setParameter('est', 'completado')
            ->getQuery()
            ->getResult();
    }

    public function findAllAceptados($trabajador)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado =:est ')
            ->andWhere('p.trabajador = :tra')
            ->setParameter('est', 'aceptado')
            ->setParameter('tra', $trabajador)
            ->getQuery()
            ->getResult();
    }

    public function findPedDia($fecha)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado =:est ')
            ->andWhere('p.fecha like :fe ')
            ->setParameter('est', 'completado')
            ->setParameter('fe', $fecha . "%")
            ->getQuery()
            ->getResult();
    }

    public function findPedMes($fecha)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado =:est ')
            ->andWhere('p.fecha like :fe ')
            ->setParameter('est', 'completado')
            ->setParameter('fe', $fecha . "%")
            ->getQuery()
            ->getResult();
    }

    public function findPedConfUser($user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado =:est')
            ->andWhere('p.cliente =:cli')
            ->setParameter('est', 'confección')
            ->setParameter('cli', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
