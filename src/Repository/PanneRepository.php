<?php

namespace App\Repository;

use App\Entity\Panne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panne>
 *
 * @method Panne|null find($id, $lockMode = null, $lockVersion = null)
 * @method Panne|null findOneBy(array $criteria, array $orderBy = null)
 * @method Panne[]    findAll()
 * @method Panne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PanneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panne::class);
    }
}
