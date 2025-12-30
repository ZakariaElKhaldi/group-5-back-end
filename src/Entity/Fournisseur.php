<?php

namespace App\Entity;

use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fournisseur:read', 'piece:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['fournisseur:read', 'piece:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fournisseur:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['fournisseur:read'])]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['fournisseur:read'])]
    private ?string $adresse = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['fournisseur:read'])]
    private ?int $delaiLivraison = null; // Days for delivery

    #[ORM\Column]
    #[Groups(['fournisseur:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: Piece::class)]
    private Collection $pieces;

    public function __construct()
    {
        $this->pieces = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getDelaiLivraison(): ?int
    {
        return $this->delaiLivraison;
    }

    public function setDelaiLivraison(?int $delaiLivraison): static
    {
        $this->delaiLivraison = $delaiLivraison;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Piece>
     */
    public function getPieces(): Collection
    {
        return $this->pieces;
    }

    public function addPiece(Piece $piece): static
    {
        if (!$this->pieces->contains($piece)) {
            $this->pieces->add($piece);
            $piece->setFournisseur($this);
        }
        return $this;
    }

    public function removePiece(Piece $piece): static
    {
        if ($this->pieces->removeElement($piece)) {
            if ($piece->getFournisseur() === $this) {
                $piece->setFournisseur(null);
            }
        }
        return $this;
    }
}
