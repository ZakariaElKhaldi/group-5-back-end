<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use App\Repository\InterventionRepository;
use App\Repository\TechnicienRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private MachineRepository $machineRepository,
        private InterventionRepository $interventionRepository,
        private TechnicienRepository $technicienRepository
    ) {
    }

    #[Route('/stats', name: 'api_dashboard_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        // Machine counts by status
        $machinesByStatus = $this->machineRepository->createQueryBuilder('m')
            ->select('m.statut, COUNT(m.id) as count')
            ->groupBy('m.statut')
            ->getQuery()
            ->getResult();

        // Intervention counts by status
        $interventionsByStatus = $this->interventionRepository->createQueryBuilder('i')
            ->select('i.statut, COUNT(i.id) as count')
            ->groupBy('i.statut')
            ->getQuery()
            ->getResult();

        // Available technicians
        $availableTechniciens = $this->technicienRepository->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.statut = :statut')
            ->setParameter('statut', 'Disponible')
            ->getQuery()
            ->getSingleScalarResult();

        // Urgent interventions in progress
        $urgentInterventions = $this->interventionRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.priorite = :priorite')
            ->andWhere('i.statut IN (:statuts)')
            ->setParameter('priorite', 'Urgente')
            ->setParameter('statuts', ['En attente', 'En cours'])
            ->getQuery()
            ->getSingleScalarResult();

        // Total maintenance cost
        $totalCost = $this->interventionRepository->createQueryBuilder('i')
            ->select('SUM(i.coutTotal)')
            ->where('i.statut = :statut')
            ->setParameter('statut', 'Terminee')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return $this->json([
            'machines' => [
                'byStatus' => $machinesByStatus,
                'total' => array_sum(array_column($machinesByStatus, 'count'))
            ],
            'interventions' => [
                'byStatus' => $interventionsByStatus,
                'total' => array_sum(array_column($interventionsByStatus, 'count')),
                'urgent' => (int) $urgentInterventions
            ],
            'techniciens' => [
                'available' => (int) $availableTechniciens
            ],
            'costs' => [
                'total' => round((float) $totalCost, 2)
            ]
        ]);
    }

    #[Route('/charts', name: 'api_dashboard_charts', methods: ['GET'])]
    public function charts(): JsonResponse
    {
        // Interventions by month (last 12 months) - Using Native SQL for DATE_FORMAT
        $conn = $this->interventionRepository->getEntityManager()->getConnection();

        $sql = "
            SELECT DATE_FORMAT(date_debut, '%Y-%m') as month, COUNT(id) as count 
            FROM intervention 
            WHERE date_debut >= :startDate 
            GROUP BY month 
            ORDER BY month ASC
        ";

        $stmt = $conn->executeQuery($sql, [
            'startDate' => (new \DateTime('-12 months'))->format('Y-m-d H:i:s')
        ]);

        $interventionsByMonth = $stmt->fetchAllAssociative();

        // Interventions by type
        $interventionsByType = $this->interventionRepository->createQueryBuilder('i')
            ->select('i.type, COUNT(i.id) as count')
            ->groupBy('i.type')
            ->getQuery()
            ->getResult();

        // Top 5 machines with most interventions
        $topMachines = $this->interventionRepository->createQueryBuilder('i')
            ->select('m.reference, m.modele, COUNT(i.id) as interventionCount')
            ->leftJoin('i.machine', 'm')
            ->groupBy('m.id')
            ->orderBy('interventionCount', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->json([
            'interventionsByMonth' => $interventionsByMonth,
            'interventionsByType' => $interventionsByType,
            'topMachines' => $topMachines
        ]);
    }
}
