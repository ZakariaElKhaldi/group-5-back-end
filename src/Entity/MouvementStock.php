<?php

namespace App\Entity;

use App\Repository\MouvementStockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MouvementStockRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MouvementStock
{
    public const TYPE_ENTREE = 'entree';
    public const TYPE_SORTIE = 'sortie';
    public const TYPE_AJUSTEMENT = 'ajustement';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['mouvement_stock:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementsStock')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['mouvement_stock:read'])]
    private ?Piece $piece = null;

    #[ORM\Column(length: 20)]
    #[Groups(['mouvement_stock:read'])]
    private ?string $type = null; // entree, sortie, ajustement

    #[ORM\Column]
    #[Groups(['mouvement_stock:read'])]
    private int $quantite = 0;

    #[ORM\Column]
    #[Groups(['mouvement_stock:read'])]
    private int $quantiteAvant = 0;

    #[ORM\Column]
    #[Groups(['mouvement_stock:read'])]
    private int $quantiteApres = 0;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['mouvement_stock:read'])]
    private ?string $motif = null; // Reason, e.g., "Intervention #42"

    #[ORM\Column]
    #[Groups(['mouvement_stock:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
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

    public function getPiece(): ?Piece
    {
        return $this->piece;
    }

    public function setPiece(?Piece $piece): static
    {
        $this->piece = $piece;
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

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getQuantiteAvant(): int
    {
        return $this->quantiteAvant;
    }

    public function setQuantiteAvant(int $quantiteAvant): static
    {
        $this->quantiteAvant = $quantiteAvant;
        return $this;
    }

    public function getQuantiteApres(): int
    {
        return $this->quantiteApres;
    }

    public function setQuantiteApres(int $quantiteApres): static
    {
        $this->quantiteApres = $quantiteApres;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Factory method to create a stock movement
     */
    public static function create(Piece $piece, string $type, int $quantite, ?string $motif = null): self
    {
        $mouvement = new self();
        $mouvement->setPiece($piece);
        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setQuantiteAvant($piece->getQuantiteStock());

        // Calculate quantity after based on type
        $quantiteApres = match ($type) {
            self::TYPE_ENTREE => $piece->getQuantiteStock() + $quantite,
            self::TYPE_SORTIE => $piece->getQuantiteStock() - $quantite,
            self::TYPE_AJUSTEMENT => $quantite, // For adjustment, the quantity IS the new stock level
            default => $piece->getQuantiteStock(),
        };

        $mouvement->setQuantiteApres($quantiteApres);
        $mouvement->setMotif($motif);

        return $mouvement;
    }
}
