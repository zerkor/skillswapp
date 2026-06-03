<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EducationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Représente le parcours académique d'un utilisateur.
 * Séparé des compétences pour éviter la confusion lors du matching.
 */
#[ORM\Entity(repositoryClass: EducationRepository::class)]
class Education
{
    public const NIVEAUX = [
        'bac'       => 'Bac',
        'bac+2'     => 'Bac+2 (BTS/DUT/BUT)',
        'bac+3'     => 'Bac+3 (Licence)',
        'bac+4'     => 'Bac+4 (Master 1)',
        'bac+5'     => 'Bac+5 (Master 2 / Ingénieur)',
        'doctorat'  => 'Doctorat',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'educations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le diplôme est requis.')]
    #[Assert\Length(max: 150)]
    private ?string $diplome = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(max: 200)]
    private ?string $etablissement = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(choices: ['bac', 'bac+2', 'bac+3', 'bac+4', 'bac+5', 'doctorat'])]
    private ?string $niveau = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $annee = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getDiplome(): ?string { return $this->diplome; }
    public function setDiplome(string $diplome): static { $this->diplome = $diplome; return $this; }

    public function getEtablissement(): ?string { return $this->etablissement; }
    public function setEtablissement(?string $etablissement): static { $this->etablissement = $etablissement; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $niveau): static { $this->niveau = $niveau; return $this; }

    public function getNiveauLabel(): string
    {
        return self::NIVEAUX[$this->niveau] ?? ($this->niveau ?? '');
    }

    public function getAnnee(): ?string { return $this->annee; }
    public function setAnnee(?string $annee): static { $this->annee = $annee; return $this; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'diplome'       => $this->diplome,
            'etablissement' => $this->etablissement,
            'niveau'        => $this->niveau,
            'niveauLabel'   => $this->getNiveauLabel(),
            'annee'         => $this->annee,
        ];
    }
}
