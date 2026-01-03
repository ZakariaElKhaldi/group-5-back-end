<?php

namespace App\Repository;

use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Settings>
 */
class SettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Settings::class);
    }

    /**
     * Get a setting value by key
     */
    public function getValue(string $key, ?string $default = null): ?string
    {
        $setting = $this->findOneBy(['settingKey' => $key]);
        return $setting?->getSettingValue() ?? $default;
    }

    /**
     * Set a setting value
     */
    public function setValue(string $key, ?string $value, string $category = 'general', ?string $description = null): void
    {
        $em = $this->getEntityManager();
        $setting = $this->findOneBy(['settingKey' => $key]);

        if (!$setting) {
            $setting = new Settings();
            $setting->setSettingKey($key);
            $setting->setCategory($category);
            if ($description) {
                $setting->setDescription($description);
            }
        }

        $setting->setSettingValue($value);
        $em->persist($setting);
        $em->flush();
    }

    /**
     * Get all settings by category
     */
    public function getByCategory(string $category): array
    {
        return $this->findBy(['category' => $category]);
    }

    /**
     * Get company info as array
     */
    public function getCompanyInfo(): array
    {
        $settings = $this->getByCategory('company');
        $result = [];

        foreach ($settings as $setting) {
            $key = str_replace('company_', '', $setting->getSettingKey());
            $result[$key] = $setting->getSettingValue();
        }

        // Default values if not set
        return array_merge([
            'name' => 'MaintenancePro SARL',
            'address' => '123 Boulevard Mohammed V, Casablanca 20000, Maroc',
            'phone' => '+212 522 123 456',
            'email' => 'contact@maintenancepro.ma',
            'ice' => '001234567890123',
            'rc' => 'RC 123456',
            'patente' => '12345678',
            'if' => '12345678',
        ], $result);
    }
}
