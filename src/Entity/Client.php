<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read', 'machine:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'machine:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['client:read'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['client:read'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['client:read'])]
    private ?string $ice = null; // Identifiant Commun de l'Entreprise (Morocco)

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['client:read'])]
    private ?string $rc = null; // Registre du Commerce

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['client:read'])]
    private ?string $patente = null; // Taxe Professionnelle

    #[ORM\Column]
    #[Groups(['client:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Machine::class)]
    private Collection $machines;

    public function __construct()
    {
        $this->machines = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Machine>
     */
    public function getMachines(): Collection
    {
        return $this->machines;
    }

    public function addMachine(Machine $machine): static
    {
        if (!$this->machines->contains($machine)) {
            $this->machines->add($machine);
            $machine->setClient($this);
        }

        return $this;
    }

    public function removeMachine(Machine $machine): static
    {
        if ($this->machines->removeElement($machine)) {
            if ($machine->getClient() === $this) {
                $machine->setClient(null);
            }
        }

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getIce(): ?string
    {
        return $this->ice;
    }

    public function setIce(?string $ice): static
    {
        $this->ice = $ice;

        return $this;
    }

    public function getRc(): ?string
    {
        return $this->rc;
    }

    public function setRc(?string $rc): static
    {
        $this->rc = $rc;

        return $this;
    }

    public function getPatente(): ?string
    {
        return $this->patente;
    }

    public function setPatente(?string $patente): static
    {
        $this->patente = $patente;

        return $this;
    }
}
