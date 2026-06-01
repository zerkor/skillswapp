<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AvailabilityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvailabilityRepository::class)]
class Availability
{
    public const JOURS = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'];

    public const JOURS_LABELS = [
        'lun' => 'Lundi',
        'mar' => 'Mardi',
        'mer' => 'Mercredi',
        'jeu' => 'Jeudi',
        'ven' => 'Vendredi',
        'sam' => 'Samedi',
        'dim' => 'Dimanche',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 3)]
    #[Assert\Choice(choices: self::JOURS)]
    private ?string $jourSemaine = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heureDebut = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heureFin = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getJourSemaine(): ?string { return $this->jourSemaine; }
    public function setJourSemaine(string $jourSemaine): static { $this->jourSemaine = $jourSemaine; return $this; }

    public function getHeureDebut(): ?\DateTimeImmutable { return $this->heureDebut; }
    public function setHeureDebut(\DateTimeImmutable $heureDebut): static { $this->heureDebut = $heureDebut; return $this; }

    public function getHeureFin(): ?\DateTimeImmutable { return $this->heureFin; }
    public function setHeureFin(\DateTimeImmutable $heureFin): static { $this->heureFin = $heureFin; return $this; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'jourSemaine' => $this->jourSemaine,
            'jourLabel'   => self::JOURS_LABELS[$this->jourSemaine] ?? $this->jourSemaine,
            'heureDebut'  => $this->heureDebut?->format('H:i'),
            'heureFin'    => $this->heureFin?->format('H:i'),
        ];
    }
}
