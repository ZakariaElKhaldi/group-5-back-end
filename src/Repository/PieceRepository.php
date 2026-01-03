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

    public function findBySearch(array $params)
    {
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $search = $params['search'] ?? null;

        $qb = $this->createQueryBuilder('p');

        if ($search) {
            $qb->andWhere('p.reference LIKE :search OR p.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $countQb = clone $qb;
        $totalItems = $countQb->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('p.nom', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'items' => $qb->getQuery()->getResult(),
            'total' => (int) $totalItems,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($totalItems / $limit)
        ];
    }
}
