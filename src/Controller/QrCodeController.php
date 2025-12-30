<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

#[Route('/api/qr')]
class QrCodeController extends AbstractController
{
    public function __construct(
        private MachineRepository $machineRepository,
        private Environment $twig
    ) {
    }

    /**
     * Generate QR code image for a machine (SVG format - no GD required)
     */
    #[Route('/machine/{id}', name: 'api_qr_machine', methods: ['GET'])]
    public function generateQrCode(int $id): Response
    {
        $machine = $this->machineRepository->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $qrContent = sprintf(
            "Machine: %s\nRef: %s\nModèle: %s\nClient: %s",
            $machine->getId(),
            $machine->getReference(),
            $machine->getModele(),
            $machine->getClient()?->getNom() ?? 'N/A'
        );

        // Build QR code using SvgWriter (no GD required)
        $builder = new Builder(
            writer: new SvgWriter(),
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => $result->getMimeType()]
        );
    }

    /**
     * Generate printable label PDF with QR code for a machine
     */
    #[Route('/machine/{id}/label', name: 'api_qr_machine_label', methods: ['GET'])]
    public function generateLabel(int $id): Response
    {
        $machine = $this->machineRepository->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $qrContent = sprintf(
            "Machine: %s\nRef: %s\nModèle: %s",
            $machine->getId(),
            $machine->getReference(),
            $machine->getModele()
        );

        // Build QR code using SvgWriter
        $builder = new Builder(
            writer: new SvgWriter(),
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 150,
            margin: 5,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $qrResult = $builder->build();
        $qrBase64 = $qrResult->getDataUri();

        // Render HTML label
        $html = $this->twig->render('pdf/machine_label.html.twig', [
            'machine' => $machine,
            'qr_code' => $qrBase64,
        ]);

        // Generate PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 280, 140], 'landscape');
        $dompdf->render();

        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf(
            'inline; filename="label_machine_%s.pdf"',
            $machine->getReference()
        ));

        return $response;
    }
}


