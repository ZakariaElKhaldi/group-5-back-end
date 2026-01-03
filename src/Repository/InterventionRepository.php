<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 *
 * @method Intervention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intervention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intervention[]    findAll()
 * @method Intervention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    public function findBySearch(array $params)
    {
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $search = $params['search'] ?? null;
        $statut = $params['statut'] ?? null;
        $priorite = $params['priorite'] ?? null;
        $technicienId = (int) ($params['technicien'] ?? 0);

        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.machine', 'm')
            ->leftJoin('i.technicien', 't')
            ->leftJoin('t.user', 'u');

        if ($search) {
            $qb->andWhere('i.description LIKE :search OR m.reference LIKE :search OR m.modele LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('i.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($priorite) {
            $qb->andWhere('i.priorite = :priorite')
                ->setParameter('priorite', $priorite);
        }

        if ($technicienId > 0) {
            // Show interventions assigned to this technician OR unassigned (available to accept)
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('t.id', ':technicienId'),
                    $qb->expr()->isNull('i.technicien')
                )
            )
                ->setParameter('technicienId', $technicienId);
        }

        // Clone for counting
        $countQb = clone $qb;
        $totalItems = $countQb->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('i.dateDebut', 'DESC')
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
