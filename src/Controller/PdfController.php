<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Machine;
use App\Repository\InterventionRepository;
use App\Repository\MachineRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/pdf')]
class PdfController extends AbstractController
{
    #[Route('/intervention/{id}', name: 'api_pdf_intervention', methods: ['GET'])]
    public function interventionReport(Intervention $intervention): Response
    {
        $html = $this->renderView('pdf/intervention.html.twig', [
            'intervention' => $intervention,
        ]);

        return $this->generatePdf($html, 'intervention_' . $intervention->getId());
    }

    #[Route('/machine/{id}', name: 'api_pdf_machine', methods: ['GET'])]
    public function machineHistory(Machine $machine): Response
    {
        $html = $this->renderView('pdf/machine_history.html.twig', [
            'machine' => $machine,
        ]);

        return $this->generatePdf($html, 'machine_history_' . $machine->getId());
    }

    private function generatePdf(string $html, string $filename): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"',
            ]
        );
    }
}
