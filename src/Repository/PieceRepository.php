<?php

namespace App\Repository;

use App\Entity\Piece;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Piece>
 */
class PieceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Piece::class);
    }

    /**
     * Find all pieces with low stock (below or at threshold)
     * @return Piece[]
     */
    public function findLowStock(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.quantiteStock <= p.seuilAlerte')
            ->orderBy('p.quantiteStock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search pieces by reference or name
     * @return Piece[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.reference LIKE :query OR p.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
