<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\JournalEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * JournalEntry represents a culture journal entry for a reservoir.
 * Users can add notes and optionally attach photos to document their cultivation activities.
 * 
 * Each entry belongs to a reservoir and is automatically timestamped on creation.
 * 
 * Security:
 * - Users can only access journal entries for reservoirs they own
 * - Entries are automatically filtered by user ownership via JournalEntryQueryExtension
 * - Creation requires ownership of the target reservoir (checked via securityPostDenormalize)
 * 
 * Usage:
 * - POST /api/journal_entries with { "reservoir": "/api/reservoirs/{id}", "content": "...", "photoUrl": "..." }
 * - GET /api/journal_entries to list all entries for user's reservoirs
 * - GET /api/journal_entries/{id} to view a specific entry
 * - PUT /api/journal_entries/{id} to update an entry
 * - DELETE /api/journal_entries/{id} to remove an entry
 * 
 * @see JournalEntryQueryExtension
 * @see Reservoir
 */
#[ORM\Entity(repositoryClass: JournalEntryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user",
            normalizationContext: ['groups' => ['journal:read', 'journal:item']]
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['journal:read']]
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['journal:write']],
            securityPostDenormalize: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user"
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user",
            denormalizationContext: ['groups' => ['journal:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user"
        )
    ]
)]
class JournalEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['journal:read'])]
    private ?int $id = null;

    /**
     * The reservoir this journal entry belongs to.
     * Each entry must be associated with a reservoir owned by the current user.
     */
    #[ORM\ManyToOne(inversedBy: 'journalEntries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'entrée de journal doit être liée à un réservoir.')]
    #[Groups(['journal:read', 'journal:write'])]
    private ?Reservoir $reservoir = null;

    /**
     * The text content of the journal entry.
     * Can contain notes, observations, actions taken, etc.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu de l\'entrée ne peut pas être vide.')]
    #[Assert\Length(
        min: 1,
        max: 5000,
        minMessage: 'Le contenu doit contenir au moins {{ limit }} caractère.',
        maxMessage: 'Le contenu ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['journal:read', 'journal:write'])]
    private ?string $content = null;

    /**
     * Optional URL or path to an attached photo.
     * Can be used to store images documenting the cultivation state.
     */
    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: 'L\'URL de la photo ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['journal:read', 'journal:write'])]
    private ?string $photoUrl = null;

    /**
     * Timestamp of when this entry was created.
     * Automatically set on entity creation via lifecycle callback.
     */
    #[ORM\Column]
    #[Groups(['journal:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Timestamp of when this entry was last updated.
     * Automatically updated on entity modification via lifecycle callback.
     */
    #[ORM\Column]
    #[Groups(['journal:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservoir(): ?Reservoir
    {
        return $this->reservoir;
    }

    public function setReservoir(?Reservoir $reservoir): static
    {
        $this->reservoir = $reservoir;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): static
    {
        $this->photoUrl = $photoUrl;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Automatically update the updatedAt timestamp before persisting changes.
     */
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
