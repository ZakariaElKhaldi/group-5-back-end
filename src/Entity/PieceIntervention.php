<?php

namespace App\Entity;

use App\Repository\PieceInterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PieceInterventionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PieceIntervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['piece_intervention:read', 'intervention:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pieceInterventions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['piece_intervention:read', 'intervention:read'])]
    private ?Piece $piece = null;

    #[ORM\ManyToOne(inversedBy: 'piecesUtilisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\Column]
    #[Groups(['piece_intervention:read', 'intervention:read'])]
    private int $quantite = 1;

    #[ORM\Column]
    #[Groups(['piece_intervention:read', 'intervention:read'])]
    private ?float $prixUnitaireApplique = null; // Price frozen at time of usage

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['piece_intervention:read', 'intervention:read'])]
    private ?\DateTimeInterface $dateUtilisation = null;

    public function __construct()
    {
        $this->dateUtilisation = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setDateUtilisationValue(): void
    {
        if ($this->dateUtilisation === null) {
            $this->dateUtilisation = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPiece(): ?Piece
    {
        return $this->piece;
    }

    public function setPiece(?Piece $piece): static
    {
        $this->piece = $piece;
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

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixUnitaireApplique(): ?float
    {
        return $this->prixUnitaireApplique;
    }

    public function setPrixUnitaireApplique(float $prixUnitaireApplique): static
    {
        $this->prixUnitaireApplique = $prixUnitaireApplique;
        return $this;
    }

    public function getDateUtilisation(): ?\DateTimeInterface
    {
        return $this->dateUtilisation;
    }

    public function setDateUtilisation(\DateTimeInterface $dateUtilisation): static
    {
        $this->dateUtilisation = $dateUtilisation;
        return $this;
    }

    /**
     * Get total cost for this line (quantity * unit price)
     */
    #[Groups(['piece_intervention:read', 'intervention:read'])]
    public function getCoutLigne(): float
    {
        return $this->quantite * ($this->prixUnitaireApplique ?? 0);
    }
}
