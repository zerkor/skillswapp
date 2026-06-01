<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    public const TYPE_TEACH = 'teach';
    public const TYPE_LEARN = 'learn';

    public const TYPES = [self::TYPE_TEACH, self::TYPE_LEARN];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1])]
    #[Assert\Range(min: 1, max: 4)]
    private int $niveau = 1;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: self::TYPES)]
    private string $type = self::TYPE_TEACH;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $categorie): static { $this->categorie = $categorie; return $this; }

    public function getNiveau(): int { return $this->niveau; }
    public function setNiveau(int $niveau): static { $this->niveau = $niveau; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'nom'       => $this->nom,
            'categorie' => $this->categorie,
            'niveau'    => $this->niveau,
            'type'      => $this->type,
            'userId'    => $this->user?->getId(),
        ];
    }
}
