<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Find clients with pagination and search
     */
    public function findBySearch(array $params): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $search = $params['search'] ?? '';

        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.nom LIKE :search OR c.email LIKE :search OR c.telephone LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Get total count
        $countQb = clone $qb;
        $total = $countQb->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        // Get paginated results
        $qb->orderBy('c.nom', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return [
            'items' => $items,
            'total' => (int) $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit)
        ];
    }
}
