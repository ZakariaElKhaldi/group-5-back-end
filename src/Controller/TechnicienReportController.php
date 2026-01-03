<?php

namespace App\Controller;

use App\Repository\TechnicienRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reports')]
class TechnicienReportController extends AbstractController
{
    public function __construct(
        private TechnicienRepository $technicienRepository
    ) {
    }

    #[Route('/technicien/{id}/pdf', name: 'api_report_technicien_pdf', methods: ['GET'])]
    public function generatePdf(int $id): Response
    {
        $technicien = $this->technicienRepository->find($id);
        if (!$technicien) {
            return $this->json(['error' => 'Technicien not found'], Response::HTTP_NOT_FOUND);
        }

        $interventions = $technicien->getInterventions();

        $totalRevenue = 0;
        foreach ($interventions as $i) {
            if ($i->getStatut() === 'Terminee') {
                $totalRevenue += $i->getCoutMainOeuvre();
            }
        }

        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('pdf/technicien_report.html.twig', [
            'technicien' => $technicien,
            'interventions' => $interventions,
            'totalRevenue' => $totalRevenue
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="report_technicien_' . $id . '.pdf"');

        return $response;
    }
}
