<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiProperty;
use App\Dto\CsvImportInput;
use App\Repository\ReservoirRepository;
use App\State\CsvImportProcessor;
use App\State\CsvImportProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reservoir represents a nutrient tank in a farm.
 * Each reservoir belongs to a farm and contains measurements.
 */
#[ORM\Entity(repositoryClass: ReservoirRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and object.farm.owner == user",
            normalizationContext: ['groups' => ['reservoir:read', 'reservoir:item']]
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['reservoir:read']]
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['reservoir:write']],
            securityPostDenormalize: "is_granted('ROLE_USER') and object.farm.owner == user"
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.farm.owner == user",
            denormalizationContext: ['groups' => ['reservoir:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.farm.owner == user"
        ),
        new Post(
            uriTemplate: '/reservoirs/{id}/measurements/import',
            input: CsvImportInput::class,
            output: false,
            provider: CsvImportProvider::class,
            processor: CsvImportProcessor::class,
            deserialize: false,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                summary: 'Import measurements from CSV file',
                description: 'Upload a CSV file to import multiple measurements for this reservoir. CSV format: measuredAt;ph;ec;waterTemp',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'description' => 'CSV file with measurements (separator: ;)'
                                    ]
                                ]
                            ]
                        ]
                    ])
                ),
                responses: [
                    '200' => [
                        'description' => 'Import successful',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'imported' => ['type' => 'integer'],
                                        'skipped' => ['type' => 'integer'],
                                        'errors' => ['type' => 'array', 'items' => ['type' => 'string']]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => ['description' => 'Invalid CSV format or missing file'],
                    '404' => ['description' => 'Reservoir not found']
                ]
            ),
            name: 'csv_import'
        )
    ]
)]
class Reservoir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservoir:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du réservoir ne peut pas être vide.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['reservoir:read', 'reservoir:write', 'measurement:read', 'farm:item'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Farm::class, inversedBy: 'reservoirs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le réservoir doit appartenir à une ferme.')]
    #[Groups(['reservoir:read', 'reservoir:write'])]
    private ?Farm $farm = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le volume ne peut pas être vide.')]
    #[Assert\Positive(message: 'Le volume doit être supérieur à zéro.')]
    #[Groups(['reservoir:read', 'reservoir:write', 'farm:item'])]
    private ?float $volumeLiters = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['reservoir:read', 'reservoir:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['reservoir:read', 'reservoir:write'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['reservoir:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['reservoir:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Measurement::class, mappedBy: 'reservoir', orphanRemoval: true)]
    #[Groups(['reservoir:item'])]
    private Collection $measurements;

    public function __construct()
    {
        $this->measurements = new ArrayCollection();
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

    public function getFarm(): ?Farm
    {
        return $this->farm;
    }

    public function setFarm(?Farm $farm): static
    {
        $this->farm = $farm;

        return $this;
    }

    public function getVolumeLiters(): ?float
    {
        return $this->volumeLiters;
    }

    public function setVolumeLiters(float $volumeLiters): static
    {
        $this->volumeLiters = $volumeLiters;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

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

    /**
     * @return Collection<int, Measurement>
     */
    public function getMeasurements(): Collection
    {
        return $this->measurements;
    }

    public function addMeasurement(Measurement $measurement): static
    {
        if (!$this->measurements->contains($measurement)) {
            $this->measurements->add($measurement);
            $measurement->setReservoir($this);
        }

        return $this;
    }

    public function removeMeasurement(Measurement $measurement): static
    {
        if ($this->measurements->removeElement($measurement)) {
            // set the owning side to null (unless already changed)
            if ($measurement->getReservoir() === $this) {
                $measurement->setReservoir(null);
            }
        }

        return $this;
    }
}
