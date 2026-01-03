<?php

namespace App\Repository;

use App\Entity\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Machine>
 *
 * @method Machine|null find($id, $lockMode = null, $lockVersion = null)
 * @method Machine|null findOneBy(array $criteria, array $orderBy = null)
 * @method Machine[]    findAll()
 * @method Machine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MachineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    public function findBySearch(array $params)
    {
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $search = $params['search'] ?? null;
        $statut = $params['statut'] ?? null;

        $qb = $this->createQueryBuilder('m');

        if ($search) {
            $qb->andWhere('m.reference LIKE :search OR m.modele LIKE :search OR m.departement LIKE :search OR m.numeroSerie LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('m.statut = :statut')
                ->setParameter('statut', $statut);
        }

        $countQb = clone $qb;
        $totalItems = $countQb->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('m.id', 'DESC')
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
