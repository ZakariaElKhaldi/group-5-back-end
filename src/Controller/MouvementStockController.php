<?php

namespace App\Controller;

use App\Repository\MouvementStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/mouvements-stock')]
class MouvementStockController extends AbstractController
{
    public function __construct(
        private MouvementStockRepository $mouvementStockRepository,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'api_mouvements_stock_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $pieceId = $request->query->get('pieceId');
        $limit = (int) $request->query->get('limit', 100);

        if ($pieceId) {
            $mouvements = $this->mouvementStockRepository->findByPiece((int) $pieceId, $limit);
        } else {
            $mouvements = $this->mouvementStockRepository->findRecent($limit);
        }

        $data = $this->serializer->serialize($mouvements, 'json', ['groups' => 'mouvement_stock:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
