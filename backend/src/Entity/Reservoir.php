<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Dto\CsvImportInput;
use App\Repository\ReservoirRepository;
use App\State\CsvImportProcessor;
use App\State\CsvImportProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservoirRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['reservoir:read']]),
        new GetCollection(normalizationContext: ['groups' => ['reservoir:read']]),
        new Post(
            denormalizationContext: ['groups' => ['reservoir:write']],
            securityPostDenormalize: "is_granted('ROLE_USER')"
        ),
        new Put(
            denormalizationContext: ['groups' => ['reservoir:write']],
            security: "is_granted('ROLE_USER')"
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
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
    #[Groups(['reservoir:read', 'reservoir:write', 'measurement:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['reservoir:read', 'reservoir:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['reservoir:read', 'reservoir:write'])]
    private ?float $capacity = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['reservoir:read', 'reservoir:write'])]
    private ?string $location = null;

    #[ORM\OneToMany(targetEntity: Measurement::class, mappedBy: 'reservoir', orphanRemoval: true)]
    private Collection $measurements;

    public function __construct()
    {
        $this->measurements = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCapacity(): ?float
    {
        return $this->capacity;
    }

    public function setCapacity(?float $capacity): static
    {
        $this->capacity = $capacity;

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
