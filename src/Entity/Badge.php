<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BadgeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: BadgeRepository::class)]
#[UniqueEntity(fields: ['nom'])]
class Badge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $nom = null;

    /** Description courte affichée dans l'interface */
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * Condition d'obtention explicite — affichée dans le tooltip.
     * Ex : "Compléter 5 sessions en tant que tuteur"
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $conditionLabel = null;

    #[ORM\Column(length: 50)]
    private string $icone = '🏆';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $conditionType = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $conditionValue = 1;

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getConditionLabel(): ?string { return $this->conditionLabel; }
    public function setConditionLabel(?string $conditionLabel): static { $this->conditionLabel = $conditionLabel; return $this; }

    public function getIcone(): string { return $this->icone; }
    public function setIcone(string $icone): static { $this->icone = $icone; return $this; }

    public function getConditionType(): ?string { return $this->conditionType; }
    public function setConditionType(?string $conditionType): static { $this->conditionType = $conditionType; return $this; }

    public function getConditionValue(): int { return $this->conditionValue; }
    public function setConditionValue(int $conditionValue): static { $this->conditionValue = $conditionValue; return $this; }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'nom'            => $this->nom,
            'description'    => $this->description,
            'conditionLabel' => $this->conditionLabel,
            'icone'          => $this->icone,
            'conditionType'  => $this->conditionType,
            'conditionValue' => $this->conditionValue,
        ];
    }
}
