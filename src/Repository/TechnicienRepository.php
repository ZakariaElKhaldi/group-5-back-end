<?php

namespace App\Repository;

use App\Entity\Technicien;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Technicien>
 *
 * @method Technicien|null find($id, $lockMode = null, $lockVersion = null)
 * @method Technicien|null findOneBy(array $criteria, array $orderBy = null)
 * @method Technicien[]    findAll()
 * @method Technicien[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnicienRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technicien::class);
    }

    public function findBySearch(array $params)
    {
        $page = (int) ($params['page'] ?? 1);
        $limit = (int) ($params['limit'] ?? 10);
        $search = $params['search'] ?? null;
        $statut = $params['statut'] ?? null;

        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.user', 'u');

        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR t.specialite LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('t.statut = :statut')
                ->setParameter('statut', $statut);
        }

        $countQb = clone $qb;
        $totalItems = $countQb->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('u.nom', 'ASC')
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
