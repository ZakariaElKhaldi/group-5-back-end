<?php

namespace App\Controller;

use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private SettingsRepository $settingsRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Get all company settings
     */
    #[Route('/company', name: 'api_settings_company_get', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getCompanySettings(): JsonResponse
    {
        $settings = $this->settingsRepository->getCompanyInfo();
        return $this->json($settings);
    }

    /**
     * Update company settings
     */
    #[Route('/company', name: 'api_settings_company_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateCompanySettings(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $allowedKeys = ['name', 'address', 'phone', 'email', 'ice', 'rc', 'patente', 'if', 'logo_url', 'website'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $this->settingsRepository->setValue(
                    "company_$key",
                    $value,
                    'company',
                    "Company $key"
                );
            }
        }

        return $this->json([
            'success' => true,
            'message' => 'Paramètres de l\'entreprise mis à jour',
            'data' => $this->settingsRepository->getCompanyInfo()
        ]);
    }

    /**
     * Get all settings (grouped by category)
     */
    #[Route('', name: 'api_settings_all', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllSettings(): JsonResponse
    {
        $settings = $this->settingsRepository->findAll();

        $grouped = [];
        foreach ($settings as $setting) {
            $category = $setting->getCategory();
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$setting->getSettingKey()] = $setting->getSettingValue();
        }

        return $this->json($grouped);
    }

    /**
     * Update a single setting
     */
    #[Route('/{key}', name: 'api_settings_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateSetting(string $key, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['value'])) {
            return $this->json(['error' => 'Value is required'], Response::HTTP_BAD_REQUEST);
        }

        $category = $data['category'] ?? 'general';
        $description = $data['description'] ?? null;

        $this->settingsRepository->setValue($key, $data['value'], $category, $description);

        return $this->json([
            'success' => true,
            'message' => 'Paramètre mis à jour'
        ]);
    }
}
