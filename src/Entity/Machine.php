<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['machine:read', 'intervention:read', 'panne:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['machine:read', 'intervention:read', 'panne:read'])]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    #[Groups(['machine:read', 'intervention:read', 'panne:read'])]
    private ?string $modele = null;

    #[ORM\Column(length: 255)]
    #[Groups(['machine:read'])]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    #[Groups(['machine:read'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['machine:read'])]
    private ?\DateTimeInterface $dateAcquisition = null;

    #[ORM\Column(length: 50)]
    #[Groups(['machine:read', 'intervention:read'])]
    private ?string $statut = null; // En service, En maintenance, Hors service

    #[ORM\ManyToOne(inversedBy: 'machines')]
    #[Groups(['machine:read'])]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'machine', targetEntity: Intervention::class)]
    private Collection $interventions;

    #[ORM\OneToMany(mappedBy: 'machine', targetEntity: Panne::class)]
    private Collection $pannes;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->pannes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

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

    public function getDateAcquisition(): ?\DateTimeInterface
    {
        return $this->dateAcquisition;
    }

    public function setDateAcquisition(\DateTimeInterface $dateAcquisition): static
    {
        $this->dateAcquisition = $dateAcquisition;

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setMachine($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getMachine() === $this) {
                $intervention->setMachine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Panne>
     */
    public function getPannes(): Collection
    {
        return $this->pannes;
    }

    public function addPanne(Panne $panne): static
    {
        if (!$this->pannes->contains($panne)) {
            $this->pannes->add($panne);
            $panne->setMachine($this);
        }

        return $this;
    }

    public function removePanne(Panne $panne): static
    {
        if ($this->pannes->removeElement($panne)) {
            // set the owning side to null (unless already changed)
            if ($panne->getMachine() === $this) {
                $panne->setMachine(null);
            }
        }

        return $this;
    }
}
