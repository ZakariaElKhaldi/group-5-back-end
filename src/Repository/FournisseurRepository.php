<?php

namespace App\Repository;

use App\Entity\Fournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fournisseur>
 */
class FournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fournisseur::class);
    }

    public function findBySearch(array $params)
    {
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $search = $params['search'] ?? null;

        $qb = $this->createQueryBuilder('f');

        if ($search) {
            $qb->andWhere('f.nom LIKE :search OR f.email LIKE :search OR f.telephone LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $countQb = clone $qb;
        $totalItems = $countQb->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('f.nom', 'ASC')
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
