<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiProperty;
use App\Repository\FarmRepository;
use App\State\FarmProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Farm represents a farming operation owned by a user.
 * A farm can contain multiple reservoirs.
 */
#[ORM\Entity(repositoryClass: FarmRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['farm:read']]
        ),
        new Get(
            security: "is_granted('ROLE_USER') and object.owner == user",
            normalizationContext: ['groups' => ['farm:read', 'farm:item']]
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: FarmProcessor::class,
            denormalizationContext: ['groups' => ['farm:write']],
            securityPostDenormalize: "is_granted('ROLE_USER') and object.owner == user"
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.owner == user",
            denormalizationContext: ['groups' => ['farm:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.owner == user"
        )
    ],
    paginationEnabled: true
)]
class Farm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['farm:read', 'reservoir:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la ferme ne peut pas être vide.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['farm:read', 'farm:write', 'reservoir:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La ferme doit avoir un propriétaire.')]
    #[Groups(['farm:read'])]
    #[ApiProperty(security: "is_granted('ROLE_ADMIN')")]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: Reservoir::class, mappedBy: 'farm', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['farm:item'])]
    private Collection $reservoirs;

    #[ORM\Column]
    #[Groups(['farm:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['farm:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->reservoirs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Reservoir>
     */
    public function getReservoirs(): Collection
    {
        return $this->reservoirs;
    }

    public function addReservoir(Reservoir $reservoir): static
    {
        if (!$this->reservoirs->contains($reservoir)) {
            $this->reservoirs->add($reservoir);
            $reservoir->setFarm($this);
        }

        return $this;
    }

    public function removeReservoir(Reservoir $reservoir): static
    {
        if ($this->reservoirs->removeElement($reservoir)) {
            // set the owning side to null (unless already changed)
            if ($reservoir->getFarm() === $this) {
                $reservoir->setFarm(null);
            }
        }

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

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
