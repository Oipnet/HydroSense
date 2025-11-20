<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\MeasurementRepository;
use App\State\MeasurementPostProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Measurement represents a single measurement of pH, EC (electrical conductivity), 
 * and water temperature for a reservoir at a specific point in time.
 * 
 * Measurements can be created:
 * - Manually via API (source: MANUAL)
 * - Via CSV import (source: CSV_IMPORT)
 * - Via external API integration (source: API_INTEGRATION)
 * 
 * Security:
 * - Users can only access measurements from reservoirs they own
 * - Only admins can delete measurements
 * 
 * Date filtering:
 * - Use ?measuredAt[after]=2025-01-01 for from date
 * - Use ?measuredAt[before]=2025-01-31 for to date
 * - Use ?measuredAt[strictly_after] and ?measuredAt[strictly_before] for exclusive bounds
 * 
 * Reservoir filtering:
 * - Use ?reservoir=/api/reservoirs/{id} or ?reservoir={id}
 * 
 * @see MeasurementPostProcessor
 * @see MeasurementQueryExtension
 */
#[ORM\Entity(repositoryClass: MeasurementRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user",
            normalizationContext: ['groups' => ['measurement:read', 'measurement:item']]
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['measurement:read']]
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: MeasurementPostProcessor::class,
            denormalizationContext: ['groups' => ['measurement:write']]
        ),
        new Post(
            uriTemplate: '/reservoirs/{id}/measurements',
            security: "is_granted('ROLE_USER')",
            processor: MeasurementPostProcessor::class,
            denormalizationContext: ['groups' => ['measurement:write:custom']],
            name: 'reservoir_add_measurement'
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user",
            denormalizationContext: ['groups' => ['measurement:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')"
        )
    ]
)]
#[ApiFilter(DateFilter::class, properties: ['measuredAt'])]
#[ApiFilter(SearchFilter::class, properties: ['reservoir' => 'exact'])]
class Measurement
{
    public const SOURCE_MANUAL = 'MANUAL';
    public const SOURCE_CSV_IMPORT = 'CSV_IMPORT';
    public const SOURCE_API_INTEGRATION = 'API_INTEGRATION';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['measurement:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'measurements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['measurement:read', 'measurement:write', 'reservoir:item'])]
    #[Assert\NotNull(groups: ['measurement:write'])]
    private ?Reservoir $reservoir = null;

    #[ORM\Column]
    #[Groups(['measurement:read', 'measurement:write', 'measurement:write:custom'])]
    private ?\DateTimeImmutable $measuredAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['measurement:read', 'measurement:write', 'measurement:write:custom'])]
    #[Assert\Range(min: 0, max: 14, notInRangeMessage: 'pH must be between {{ min }} and {{ max }}')]
    private ?float $ph = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['measurement:read', 'measurement:write', 'measurement:write:custom'])]
    #[Assert\Positive(message: 'EC must be a positive value')]
    private ?float $ec = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['measurement:read', 'measurement:write', 'measurement:write:custom'])]
    #[Assert\Range(min: -10, max: 50, notInRangeMessage: 'Water temperature must be between {{ min }}°C and {{ max }}°C')]
    private ?float $waterTemp = null;

    #[ORM\Column(length: 50)]
    #[Groups(['measurement:read'])]
    #[Assert\Choice(choices: [self::SOURCE_MANUAL, self::SOURCE_CSV_IMPORT, self::SOURCE_API_INTEGRATION])]
    private string $source = self::SOURCE_MANUAL;

    #[ORM\Column]
    #[Groups(['measurement:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getMeasuredAt(): ?\DateTimeImmutable
    {
        return $this->measuredAt;
    }

    public function setMeasuredAt(\DateTimeImmutable $measuredAt): static
    {
        $this->measuredAt = $measuredAt;

        return $this;
    }

    public function getPh(): ?float
    {
        return $this->ph;
    }

    public function setPh(?float $ph): static
    {
        $this->ph = $ph;

        return $this;
    }

    public function getEc(): ?float
    {
        return $this->ec;
    }

    public function setEc(?float $ec): static
    {
        $this->ec = $ec;

        return $this;
    }

    public function getWaterTemp(): ?float
    {
        return $this->waterTemp;
    }

    public function setWaterTemp(?float $waterTemp): static
    {
        $this->waterTemp = $waterTemp;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

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
}
