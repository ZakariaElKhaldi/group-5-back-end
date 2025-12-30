<?php

namespace App\Repository;

use App\Entity\MouvementStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MouvementStock>
 */
class MouvementStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementStock::class);
    }

    /**
     * Find recent movements for a specific piece
     * @return MouvementStock[]
     */
    public function findByPiece(int $pieceId, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.piece = :pieceId')
            ->setParameter('pieceId', $pieceId)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all recent movements
     * @return MouvementStock[]
     */
    public function findRecent(int $limit = 100): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.piece', 'p')
            ->addSelect('p')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
