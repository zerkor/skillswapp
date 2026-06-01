<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const NIVEAU_NOVICE     = 'novice';
    public const NIVEAU_APPRENTI   = 'apprenti';
    public const NIVEAU_MENTOR     = 'mentor';
    public const NIVEAU_EXPERT     = 'expert';
    public const NIVEAU_LEGENDE    = 'legende';

    public const NIVEAUX = [
        self::NIVEAU_NOVICE,
        self::NIVEAU_APPRENTI,
        self::NIVEAU_MENTOR,
        self::NIVEAU_EXPERT,
        self::NIVEAU_LEGENDE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $formation = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $promotion = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $score = 0;

    #[ORM\Column(length: 20, options: ['default' => 'novice'])]
    private string $niveau = self::NIVEAU_NOVICE;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: Skill::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $skills;

    #[ORM\OneToMany(targetEntity: Availability::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $availabilities;

    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'tuteur', cascade: ['persist'])]
    private Collection $sessionsAsTutor;

    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'apprenant', cascade: ['persist'])]
    private Collection $sessionsAsLearner;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'auteur', cascade: ['persist'])]
    private Collection $reviews;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $posts;

    #[ORM\OneToMany(targetEntity: UserBadge::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userBadges;

    public function __construct()
    {
        $this->skills            = new ArrayCollection();
        $this->availabilities    = new ArrayCollection();
        $this->sessionsAsTutor   = new ArrayCollection();
        $this->sessionsAsLearner = new ArrayCollection();
        $this->reviews           = new ArrayCollection();
        $this->posts             = new ArrayCollection();
        $this->userBadges        = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getFullName(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }

    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $bio): static { $this->bio = $bio; return $this; }

    public function getFormation(): ?string { return $this->formation; }
    public function setFormation(?string $formation): static { $this->formation = $formation; return $this; }

    public function getPromotion(): ?string { return $this->promotion; }
    public function setPromotion(?string $promotion): static { $this->promotion = $promotion; return $this; }

    public function getScore(): int { return $this->score; }
    public function setScore(int $score): static { $this->score = $score; return $this; }
    public function addScore(int $points): static { $this->score += $points; return $this; }

    public function getNiveau(): string { return $this->niveau; }
    public function setNiveau(string $niveau): static { $this->niveau = $niveau; return $this; }

    public function isVerified(): bool { return $this->isVerified; }
    public function setIsVerified(bool $isVerified): static { $this->isVerified = $isVerified; return $this; }

    public function getVerificationToken(): ?string { return $this->verificationToken; }
    public function setVerificationToken(?string $token): static { $this->verificationToken = $token; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    /** @return Collection<int, Skill> */
    public function getSkills(): Collection { return $this->skills; }
    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setUser($this);
        }
        return $this;
    }
    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);
        return $this;
    }

    /** @return Collection<int, Availability> */
    public function getAvailabilities(): Collection { return $this->availabilities; }
    public function addAvailability(Availability $availability): static
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities->add($availability);
            $availability->setUser($this);
        }
        return $this;
    }

    /** @return Collection<int, Session> */
    public function getSessionsAsTutor(): Collection { return $this->sessionsAsTutor; }

    /** @return Collection<int, Session> */
    public function getSessionsAsLearner(): Collection { return $this->sessionsAsLearner; }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection { return $this->reviews; }

    /** @return Collection<int, Post> */
    public function getPosts(): Collection { return $this->posts; }

    /** @return Collection<int, UserBadge> */
    public function getUserBadges(): Collection { return $this->userBadges; }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'email'      => $this->email,
            'nom'        => $this->nom,
            'prenom'     => $this->prenom,
            'fullName'   => $this->getFullName(),
            'photo'      => $this->photo,
            'bio'        => $this->bio,
            'formation'  => $this->formation,
            'promotion'  => $this->promotion,
            'score'      => $this->score,
            'niveau'     => $this->niveau,
            'isVerified' => $this->isVerified,
            'createdAt'  => $this->createdAt?->format('c'),
        ];
    }
}
