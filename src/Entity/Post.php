<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    private ?string $contenu = null;

    /**
     * Compteurs de réactions multi-types — v1.1
     * Format JSON : {"like":0,"heart":0,"idea":0,"celebrate":0}
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $reactionCounts = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $competenceTag = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_likes')]
    private Collection $likes;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $comments;

    public function __construct()
    {
        $this->likes    = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(string $contenu): static { $this->contenu = $contenu; return $this; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): static { $this->imageUrl = $imageUrl; return $this; }

    public function getCompetenceTag(): ?string { return $this->competenceTag; }
    public function setCompetenceTag(?string $competenceTag): static { $this->competenceTag = $competenceTag; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getReactionCounts(): array
    {
        return $this->reactionCounts ?? ['like' => 0, 'heart' => 0, 'idea' => 0, 'celebrate' => 0];
    }

    public function addReaction(string $type): void
    {
        $counts = $this->getReactionCounts();
        $counts[$type] = ($counts[$type] ?? 0) + 1;
        $this->reactionCounts = $counts;
    }

    public function removeReaction(string $type): void
    {
        $counts = $this->getReactionCounts();
        $counts[$type] = max(0, ($counts[$type] ?? 0) - 1);
        $this->reactionCounts = $counts;
    }

    /** @return Collection<int, User> */
    public function getLikes(): Collection { return $this->likes; }

    public function addLike(User $user): static
    {
        if (!$this->likes->contains($user)) {
            $this->likes->add($user);
        }
        return $this;
    }

    public function removeLike(User $user): static
    {
        $this->likes->removeElement($user);
        return $this;
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes->contains($user);
    }

    /** @return Collection<int, Comment> */
    public function getComments(): Collection { return $this->comments; }

    public function toArray(?User $currentUser = null): array
    {
        return [
            'id'             => $this->id,
            'user'           => $this->user?->toArray(),
            'contenu'        => $this->contenu,
            'imageUrl'       => $this->imageUrl,
            'competenceTag'  => $this->competenceTag,
            'createdAt'      => $this->createdAt?->format('c'),
            'likesCount'     => $this->likes->count(),
            'commentsCount'  => $this->comments->count(),
            'isLiked'        => $currentUser ? $this->isLikedBy($currentUser) : false,
            'reactionCounts' => $this->getReactionCounts(),
        ];
    }
}
