<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserBadgeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBadgeRepository::class)]
#[ORM\UniqueConstraint(name: 'user_badge_unique', columns: ['user_id', 'badge_id'])]
class UserBadge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userBadges')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Badge::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Badge $badge = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $obtainedAt = null;

    public function __construct()
    {
        $this->obtainedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getBadge(): ?Badge { return $this->badge; }
    public function setBadge(?Badge $badge): static { $this->badge = $badge; return $this; }

    public function getObtainedAt(): ?\DateTimeImmutable { return $this->obtainedAt; }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'badge'      => $this->badge?->toArray(),
            'obtainedAt' => $this->obtainedAt?->format('c'),
        ];
    }
}
