<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['intervention:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['intervention:read'])]
    private ?Machine $machine = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[Groups(['intervention:read'])]
    private ?Technicien $technicien = null;

    #[ORM\Column(length: 50)]
    #[Groups(['intervention:read'])]
    private ?string $type = null; // preventive, corrective

    #[ORM\Column(length: 50)]
    #[Groups(['intervention:read'])]
    private ?string $priorite = null; // Normale, Elevee, Urgente

    #[ORM\Column(length: 50)]
    #[Groups(['intervention:read'])]
    private ?string $statut = null; // En attente, En cours, Terminee, Annulee

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['intervention:read'])]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['intervention:read'])]
    private ?\DateTimeInterface $dateFinPrevue = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['intervention:read'])]
    private ?\DateTimeInterface $dateFinReelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['intervention:read'])]
    private ?string $duree = null; // Stored as string like "2h 30m" or int minutes

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['intervention:read'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['intervention:read'])]
    private ?string $resolution = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['intervention:read'])]
    private ?float $coutMainOeuvre = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['intervention:read'])]
    private ?float $coutPieces = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['intervention:read'])]
    private ?float $coutTotal = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['intervention:read'])]
    private ?float $tauxHoraireApplique = null; // Frozen at creation time

    #[ORM\Column]
    #[Groups(['intervention:read'])]
    private bool $confirmationTechnicien = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['intervention:read'])]
    private ?\DateTimeInterface $confirmationTechnicienAt = null;

    #[ORM\Column]
    #[Groups(['intervention:read'])]
    private bool $confirmationClient = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['intervention:read'])]
    private ?\DateTimeInterface $confirmationClientAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }

    public function getTechnicien(): ?Technicien
    {
        return $this->technicien;
    }

    public function setTechnicien(?Technicien $technicien): static
    {
        $this->technicien = $technicien;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(string $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFinPrevue(): ?\DateTimeInterface
    {
        return $this->dateFinPrevue;
    }

    public function setDateFinPrevue(?\DateTimeInterface $dateFinPrevue): static
    {
        $this->dateFinPrevue = $dateFinPrevue;

        return $this;
    }

    public function getDateFinReelle(): ?\DateTimeInterface
    {
        return $this->dateFinReelle;
    }

    public function setDateFinReelle(?\DateTimeInterface $dateFinReelle): static
    {
        $this->dateFinReelle = $dateFinReelle;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getResolution(): ?string
    {
        return $this->resolution;
    }

    public function setResolution(?string $resolution): static
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getCoutMainOeuvre(): ?float
    {
        return $this->coutMainOeuvre;
    }

    public function setCoutMainOeuvre(?float $coutMainOeuvre): static
    {
        $this->coutMainOeuvre = $coutMainOeuvre;

        return $this;
    }

    public function getCoutPieces(): ?float
    {
        return $this->coutPieces;
    }

    public function setCoutPieces(?float $coutPieces): static
    {
        $this->coutPieces = $coutPieces;

        return $this;
    }

    public function getCoutTotal(): ?float
    {
        return $this->coutTotal;
    }

    public function setCoutTotal(?float $coutTotal): static
    {
        $this->coutTotal = $coutTotal;

        return $this;
    }

    public function getTauxHoraireApplique(): ?float
    {
        return $this->tauxHoraireApplique;
    }

    public function setTauxHoraireApplique(?float $tauxHoraireApplique): static
    {
        $this->tauxHoraireApplique = $tauxHoraireApplique;

        return $this;
    }

    public function isConfirmationTechnicien(): bool
    {
        return $this->confirmationTechnicien;
    }

    public function setConfirmationTechnicien(bool $confirmationTechnicien): static
    {
        $this->confirmationTechnicien = $confirmationTechnicien;

        return $this;
    }

    public function getConfirmationTechnicienAt(): ?\DateTimeInterface
    {
        return $this->confirmationTechnicienAt;
    }

    public function setConfirmationTechnicienAt(?\DateTimeInterface $confirmationTechnicienAt): static
    {
        $this->confirmationTechnicienAt = $confirmationTechnicienAt;

        return $this;
    }

    public function isConfirmationClient(): bool
    {
        return $this->confirmationClient;
    }

    public function setConfirmationClient(bool $confirmationClient): static
    {
        $this->confirmationClient = $confirmationClient;

        return $this;
    }

    public function getConfirmationClientAt(): ?\DateTimeInterface
    {
        return $this->confirmationClientAt;
    }

    public function setConfirmationClientAt(?\DateTimeInterface $confirmationClientAt): static
    {
        $this->confirmationClientAt = $confirmationClientAt;

        return $this;
    }

    /**
     * Calculate duration in minutes between dateDebut and dateFinReelle
     */
    public function calculateDureeMinutes(): ?int
    {
        if ($this->dateDebut === null || $this->dateFinReelle === null) {
            return null;
        }
        $diff = $this->dateDebut->diff($this->dateFinReelle);
        return ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
    }

    /**
     * Calculate costs based on duration and frozen rate
     */
    public function calculateCosts(): void
    {
        $dureeMinutes = $this->calculateDureeMinutes();
        if ($dureeMinutes !== null && $this->tauxHoraireApplique !== null) {
            $dureeHeures = $dureeMinutes / 60;
            $this->coutMainOeuvre = round($dureeHeures * $this->tauxHoraireApplique, 2);
            $this->coutTotal = round($this->coutMainOeuvre + ($this->coutPieces ?? 0), 2);
            $this->duree = sprintf('%dh %02dm', floor($dureeMinutes / 60), $dureeMinutes % 60);
        }
    }
}
