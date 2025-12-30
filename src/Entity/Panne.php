<?php

namespace App\Entity;

use App\Repository\PanneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PanneRepository::class)]
class Panne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['panne:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pannes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['panne:read'])]
    private ?Machine $machine = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['panne:read'])]
    private ?\DateTimeInterface $dateDeclaration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['panne:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Groups(['panne:read'])]
    private ?string $gravite = null; // Faible, Moyenne, Elevee

    #[ORM\Column(length: 50)]
    #[Groups(['panne:read'])]
    private ?string $statut = 'Declaree'; // Declaree, En traitement, Resolue

    #[ORM\OneToOne(cascade: ['persist'])]
    #[Groups(['panne:read'])]
    private ?Intervention $intervention = null;

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

    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->dateDeclaration;
    }

    public function setDateDeclaration(\DateTimeInterface $dateDeclaration): static
    {
        $this->dateDeclaration = $dateDeclaration;

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

    public function getGravite(): ?string
    {
        return $this->gravite;
    }

    public function setGravite(string $gravite): static
    {
        $this->gravite = $gravite;

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

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;

        return $this;
    }
}

