<?php

namespace App\Entity;

use App\Repository\InterventionLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InterventionLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class InterventionLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['intervention:read', 'log:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['intervention:read', 'log:read'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['intervention:read', 'log:read'])]
    private ?string $message = null;

    #[ORM\Column(length: 50)]
    #[Groups(['intervention:read', 'log:read'])]
    private ?string $type = 'comment'; // comment, status_change, arrival, departure

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['intervention:read', 'log:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
