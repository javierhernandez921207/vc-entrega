<?php

namespace App\Repository;

use App\Entity\Porducto;
use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Producto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Producto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Producto[]    findAll()
 * @method Producto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }

    // /**
    //  * @return Porducto[] Returns an array of Porducto objects
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
    public function findOneBySomeField($value): ?Porducto
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    /**
     * @return Producto[] Returns productos recientes
     */

    public function findUltimosProd()
    {

        return $this->createQueryBuilder('p')
            ->addSelect('RAND() as HIDDEN rand')
            ->where('p.cantidad > 0')
            ->andWhere('p.categoria !=:cat ')
            ->setParameter('cat', 'null')
            ->addOrderBy('p.registro', 'desc')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Producto[] Returns productos negocios 
     */

    public function findByNegocio($negocio)
    {

        return $this->createQueryBuilder('p')            
            ->Where('p.negocio =:neg')
            ->setParameter('neg', $negocio)
            ->addOrderBy('p.cantidad', 'desc')            
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Producto[]
     */
    public function findBySearchQuery(string $query): array
    {
        $searchTerms = $this->extractSearchTerms($query);
        if (0 === \count($searchTerms)) {
            return [];
        }
        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->where('p.nombre LIKE :t_' . $key)
                ->andWhere('p.cantidad > 0')
                ->setParameter('t_' . $key, '%' . $term . '%');
        }
        return $queryBuilder
            ->orderBy('p.registro', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Transforms the search string into an array of search terms.
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $searchQuery = trim(preg_replace('/[[:space:]]+/', ' ', $searchQuery));
        $terms = array_unique(explode(' ', $searchQuery));

        // ignore the search terms that are too short
        return array_filter($terms, function ($term) {
            return 2 <= mb_strlen($term);
        });
    }

}
