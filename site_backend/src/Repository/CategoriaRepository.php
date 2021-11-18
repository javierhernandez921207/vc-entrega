<?php

namespace App\Repository;

use App\Entity\Categoria;
use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Array_;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @method Categoria|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categoria|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categoria[]    findAll()
 * @method Categoria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoria::class);
    }

    public function findProdDep($id)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            "SELECT p 
            FROM App\Entity\Producto p
            INNER JOIN App\Entity\Categoria c
            WHERE p.categoria = c.id AND 
            c.id = " . $id . " 
            ORDER BY p.registro DESC");

        return $query->getResult();

    }

    public function findGanDia($dia, $dep)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            "SELECT p 
            FROM App\Entity\Pedido p            
            WHERE p.fecha =" . $dia . " AND
            p.estado = completado");
        return $query->getResult();
    }

    public function findProdCuadre()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            "SELECT p 
            FROM App\Entity\Producto p
            WHERE p.categoria != ''             
            ORDER BY p.categoria");
        return $query->getResult();
    }
}
