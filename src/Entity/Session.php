<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Session
{
    public const TYPE_COURS_RAPIDE = 'cours_rapide';
    public const TYPE_ATELIER      = 'atelier';
    public const TYPE_CLUB         = 'club';

    public const TYPES = [
        self::TYPE_COURS_RAPIDE => 'Cours rapide',
        self::TYPE_ATELIER      => 'Atelier',
        self::TYPE_CLUB         => 'Club',
    ];

    public const STATUT_PROPOSEE   = 'proposee';
    public const STATUT_CONFIRMEE  = 'confirmee';
    public const STATUT_EN_COURS   = 'en_cours';
    public const STATUT_COMPLETEE  = 'completee';
    public const STATUT_ANNULEE    = 'annulee';

    public const STATUTS = [
        self::STATUT_PROPOSEE,
        self::STATUT_CONFIRMEE,
        self::STATUT_EN_COURS,
        self::STATUT_COMPLETEE,
        self::STATUT_ANNULEE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessionsAsTutor')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $tuteur = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessionsAsLearner')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $apprenant = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $competence = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_COURS_RAPIDE, self::TYPE_ATELIER, self::TYPE_CLUB])]
    private string $type = self::TYPE_COURS_RAPIDE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 60])]
    #[Assert\Range(min: 15, max: 480)]
    private int $dureeMinutes = 60;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_PROPOSEE;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuOuLien = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $tuteurCompleted = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $apprenantCompleted = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTuteur(): ?User { return $this->tuteur; }
    public function setTuteur(?User $tuteur): static { $this->tuteur = $tuteur; return $this; }

    public function getApprenant(): ?User { return $this->apprenant; }
    public function setApprenant(?User $apprenant): static { $this->apprenant = $apprenant; return $this; }

    public function getCompetence(): ?string { return $this->competence; }
    public function setCompetence(string $competence): static { $this->competence = $competence; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }

    public function getDureeMinutes(): int { return $this->dureeMinutes; }
    public function setDureeMinutes(int $dureeMinutes): static { $this->dureeMinutes = $dureeMinutes; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getLieuOuLien(): ?string { return $this->lieuOuLien; }
    public function setLieuOuLien(?string $lieuOuLien): static { $this->lieuOuLien = $lieuOuLien; return $this; }

    public function isTuteurCompleted(): bool { return $this->tuteurCompleted; }
    public function setTuteurCompleted(bool $v): static { $this->tuteurCompleted = $v; return $this; }

    public function isApprenantCompleted(): bool { return $this->apprenantCompleted; }
    public function setApprenantCompleted(bool $v): static { $this->apprenantCompleted = $v; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'tuteur'            => $this->tuteur?->toArray(),
            'apprenant'         => $this->apprenant?->toArray(),
            'competence'        => $this->competence,
            'type'              => $this->type,
            'typeLabel'         => self::TYPES[$this->type] ?? $this->type,
            'date'              => $this->date?->format('c'),
            'dureeMinutes'      => $this->dureeMinutes,
            'statut'            => $this->statut,
            'lieuOuLien'        => $this->lieuOuLien,
            'tuteurCompleted'   => $this->tuteurCompleted,
            'apprenantCompleted' => $this->apprenantCompleted,
            'createdAt'         => $this->createdAt?->format('c'),
        ];
    }
}
