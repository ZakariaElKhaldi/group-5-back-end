<?php

namespace App\Entity;

use App\Repository\PieceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PieceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Piece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['piece:read', 'piece_intervention:read', 'intervention:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['piece:read', 'piece_intervention:read', 'intervention:read'])]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    #[Groups(['piece:read', 'piece_intervention:read', 'intervention:read'])]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['piece:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['piece:read', 'piece_intervention:read'])]
    private ?float $prixUnitaire = null;

    #[ORM\Column]
    #[Groups(['piece:read'])]
    private int $quantiteStock = 0;

    #[ORM\Column]
    #[Groups(['piece:read'])]
    private int $seuilAlerte = 5; // Default threshold

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['piece:read'])]
    private ?string $emplacement = null; // Storage location

    #[ORM\ManyToOne(inversedBy: 'pieces')]
    #[Groups(['piece:read'])]
    private ?Fournisseur $fournisseur = null;

    #[ORM\OneToMany(mappedBy: 'piece', targetEntity: PieceIntervention::class, orphanRemoval: true)]
    private Collection $pieceInterventions;

    #[ORM\OneToMany(mappedBy: 'piece', targetEntity: MouvementStock::class, orphanRemoval: true)]
    private Collection $mouvementsStock;

    #[ORM\Column]
    #[Groups(['piece:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->pieceInterventions = new ArrayCollection();
        $this->mouvementsStock = new ArrayCollection();
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getQuantiteStock(): int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;
        return $this;
    }

    public function getSeuilAlerte(): int
    {
        return $this->seuilAlerte;
    }

    public function setSeuilAlerte(int $seuilAlerte): static
    {
        $this->seuilAlerte = $seuilAlerte;
        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): static
    {
        $this->emplacement = $emplacement;
        return $this;
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Check if stock is below alert threshold
     */
    #[Groups(['piece:read'])]
    public function isLowStock(): bool
    {
        return $this->quantiteStock <= $this->seuilAlerte;
    }

    /**
     * Deduct stock quantity (throws exception if insufficient)
     */
    public function deduireStock(int $quantite): static
    {
        if ($quantite > $this->quantiteStock) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Stock insuffisant pour la pièce %s (demandé: %d, disponible: %d)',
                    $this->reference,
                    $quantite,
                    $this->quantiteStock
                )
            );
        }
        $this->quantiteStock -= $quantite;
        return $this;
    }

    /**
     * Add stock quantity
     */
    public function ajouterStock(int $quantite): static
    {
        $this->quantiteStock += $quantite;
        return $this;
    }

    /**
     * @return Collection<int, PieceIntervention>
     */
    public function getPieceInterventions(): Collection
    {
        return $this->pieceInterventions;
    }

    public function addPieceIntervention(PieceIntervention $pieceIntervention): static
    {
        if (!$this->pieceInterventions->contains($pieceIntervention)) {
            $this->pieceInterventions->add($pieceIntervention);
            $pieceIntervention->setPiece($this);
        }
        return $this;
    }

    public function removePieceIntervention(PieceIntervention $pieceIntervention): static
    {
        if ($this->pieceInterventions->removeElement($pieceIntervention)) {
            if ($pieceIntervention->getPiece() === $this) {
                $pieceIntervention->setPiece(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, MouvementStock>
     */
    public function getMouvementsStock(): Collection
    {
        return $this->mouvementsStock;
    }

    public function addMouvementStock(MouvementStock $mouvementStock): static
    {
        if (!$this->mouvementsStock->contains($mouvementStock)) {
            $this->mouvementsStock->add($mouvementStock);
            $mouvementStock->setPiece($this);
        }
        return $this;
    }

    public function removeMouvementStock(MouvementStock $mouvementStock): static
    {
        if ($this->mouvementsStock->removeElement($mouvementStock)) {
            if ($mouvementStock->getPiece() === $this) {
                $mouvementStock->setPiece(null);
            }
        }
        return $this;
    }
}
