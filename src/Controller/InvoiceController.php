<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route('/api/invoices')]
class InvoiceController extends AbstractController
{
    // Company details (hardcoded for this demo - should be in config/parameters)
    private const COMPANY = [
        'name' => 'MaintenancePro SARL',
        'address' => '123 Boulevard Mohammed V, Casablanca 20000, Maroc',
        'phone' => '+212 522 123 456',
        'email' => 'contact@maintenancepro.ma',
        'ice' => '001234567890123',
        'rc' => 'RC 123456',
        'patente' => '12345678',
        'if' => '12345678',
    ];

    public function __construct(
        private InterventionRepository $interventionRepository,
        private Environment $twig
    ) {
    }

    #[Route('/intervention/{id}', name: 'api_invoice_intervention', methods: ['GET'])]
    public function generateInvoice(int $id): Response
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        // Calculate totals
        $coutMainOeuvre = $intervention->getCoutMainOeuvre() ?? 0;
        $coutPieces = $intervention->getCoutPiecesCalcule() ?? 0;
        $subtotal = $coutMainOeuvre + $coutPieces;
        $tva = $subtotal * 0.20; // 20% TVA
        $totalTtc = $subtotal + $tva;

        // Get client from machine
        $client = $intervention->getMachine()?->getClient();

        // Generate invoice number: FAC-YEAR-INTERVENTIONID
        $invoiceNumber = sprintf('FAC-%s-%04d', date('Y'), $intervention->getId());

        // Render HTML
        $html = $this->twig->render('pdf/invoice_maroc.html.twig', [
            'intervention' => $intervention,
            'client' => $client,
            'company' => self::COMPANY,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => new \DateTime(),
            'subtotal' => $subtotal,
            'tva' => $tva,
            'total_ttc' => $totalTtc,
        ]);

        // Configure Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Return PDF
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf(
            'inline; filename="facture_%s.pdf"',
            $invoiceNumber
        ));

        return $response;
    }

    #[Route('/intervention/{id}/download', name: 'api_invoice_intervention_download', methods: ['GET'])]
    public function downloadInvoice(int $id): Response
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        // Calculate totals
        $coutMainOeuvre = $intervention->getCoutMainOeuvre() ?? 0;
        $coutPieces = $intervention->getCoutPiecesCalcule() ?? 0;
        $subtotal = $coutMainOeuvre + $coutPieces;
        $tva = $subtotal * 0.20;
        $totalTtc = $subtotal + $tva;

        $client = $intervention->getMachine()?->getClient();
        $invoiceNumber = sprintf('FAC-%s-%04d', date('Y'), $intervention->getId());

        $html = $this->twig->render('pdf/invoice_maroc.html.twig', [
            'intervention' => $intervention,
            'client' => $client,
            'company' => self::COMPANY,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => new \DateTime(),
            'subtotal' => $subtotal,
            'tva' => $tva,
            'total_ttc' => $totalTtc,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf(
            'attachment; filename="facture_%s.pdf"',
            $invoiceNumber
        ));

        return $response;
    }
}
