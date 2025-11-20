<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use App\Repository\AlertRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Alert represents an automated notification when measurement values 
 * fall outside the acceptable ranges defined by the CultureProfile.
 * 
 * Alerts are automatically generated when a Measurement is created
 * and its values (pH, EC, waterTemp) exceed the thresholds defined
 * in the associated CultureProfile.
 * 
 * Types of alerts:
 * - PH_OUT_OF_RANGE: pH value outside [phMin, phMax]
 * - EC_OUT_OF_RANGE: EC value outside [ecMin, ecMax]
 * - TEMP_OUT_OF_RANGE: Water temperature outside [waterTempMin, waterTempMax]
 * 
 * Severity levels:
 * - INFO: Minor deviation (future: < 10% outside range)
 * - WARN: Moderate deviation (future: 10-25% outside range)
 * - CRITICAL: Severe deviation (future: > 25% outside range)
 * 
 * Security:
 * - Users can only access alerts from reservoirs they own
 * - Alerts are automatically filtered by AlertQueryExtension
 * 
 * Filtering:
 * - ?type=PH_OUT_OF_RANGE to filter by alert type
 * - ?severity=CRITICAL to filter by severity
 * - ?resolved=false to get only unresolved alerts
 * - ?reservoir=/api/reservoirs/{id} to filter by reservoir
 * - ?createdAt[after]=2025-01-01 for date filtering
 * 
 * @see AnomalyDetector Service that generates alerts
 * @see MeasurementPostProcessor Processor that triggers alert creation
 * @see AlertQueryExtension Security filtering by owner
 */
#[ORM\Entity(repositoryClass: AlertRepository::class)]
#[ORM\Table(name: 'alert')]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user",
            normalizationContext: ['groups' => ['alert:read', 'alert:item']],
            description: 'Retrieve detailed information about a single alert including the triggering measurement.'
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['alert:read']],
            order: ['createdAt' => 'DESC'],
            description: 'Retrieve all alerts for reservoirs owned by the authenticated user. Use filters: ?resolved=false, ?severity=CRITICAL, ?type=PH_OUT_OF_RANGE, ?createdAt[after]=2025-01-01'
        ),
        new Patch(
            security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user",
            denormalizationContext: ['groups' => ['alert:update']],
            description: 'Mark an alert as resolved by setting the resolvedAt timestamp.'
        )
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 30
)]
#[ApiFilter(SearchFilter::class, properties: [
    'type' => 'exact',
    'severity' => 'exact',
    'reservoir' => 'exact',
    'resolved' => 'boolean'
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'resolvedAt'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'severity', 'type'])]
class Alert
{
    // Alert types
    public const TYPE_PH_OUT_OF_RANGE = 'PH_OUT_OF_RANGE';
    public const TYPE_EC_OUT_OF_RANGE = 'EC_OUT_OF_RANGE';
    public const TYPE_TEMP_OUT_OF_RANGE = 'TEMP_OUT_OF_RANGE';

    // Severity levels
    public const SEVERITY_INFO = 'INFO';
    public const SEVERITY_WARN = 'WARN';
    public const SEVERITY_CRITICAL = 'CRITICAL';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['alert:read'])]
    private ?int $id = null;

    /**
     * The reservoir where the anomaly was detected
     */
    #[ORM\ManyToOne(targetEntity: Reservoir::class, inversedBy: 'alerts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Alert must be associated with a reservoir.')]
    #[Groups(['alert:read'])]
    private ?Reservoir $reservoir = null;

    /**
     * The measurement that triggered this alert
     */
    #[ORM\ManyToOne(targetEntity: Measurement::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Alert must be associated with a measurement.')]
    #[Groups(['alert:read', 'alert:item'])]
    private ?Measurement $measurement = null;

    /**
     * Type of alert (PH_OUT_OF_RANGE, EC_OUT_OF_RANGE, TEMP_OUT_OF_RANGE)
     */
    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank(message: 'Alert type is required.')]
    #[Assert\Choice(
        choices: [
            self::TYPE_PH_OUT_OF_RANGE,
            self::TYPE_EC_OUT_OF_RANGE,
            self::TYPE_TEMP_OUT_OF_RANGE
        ],
        message: 'Invalid alert type.'
    )]
    #[Groups(['alert:read'])]
    private string $type;

    /**
     * Severity level (INFO, WARN, CRITICAL)
     */
    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\NotBlank(message: 'Severity is required.')]
    #[Assert\Choice(
        choices: [
            self::SEVERITY_INFO,
            self::SEVERITY_WARN,
            self::SEVERITY_CRITICAL
        ],
        message: 'Invalid severity level.'
    )]
    #[Groups(['alert:read'])]
    private string $severity;

    /**
     * Human-readable message describing the alert
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['alert:read'])]
    private string $message;

    /**
     * The measured value that triggered the alert
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['alert:read'])]
    private float $measuredValue;

    /**
     * The expected minimum value from CultureProfile
     */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['alert:read'])]
    private ?float $expectedMin = null;

    /**
     * The expected maximum value from CultureProfile
     */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['alert:read'])]
    private ?float $expectedMax = null;

    /**
     * Date and time when the alert was created
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['alert:read'])]
    private \DateTimeImmutable $createdAt;

    /**
     * Date and time when the alert was resolved (null if not resolved)
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['alert:read', 'alert:update'])]
    private ?\DateTimeImmutable $resolvedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and Setters

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

    public function getMeasurement(): ?Measurement
    {
        return $this->measurement;
    }

    public function setMeasurement(?Measurement $measurement): static
    {
        $this->measurement = $measurement;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getMeasuredValue(): float
    {
        return $this->measuredValue;
    }

    public function setMeasuredValue(float $measuredValue): static
    {
        $this->measuredValue = $measuredValue;
        return $this;
    }

    public function getExpectedMin(): ?float
    {
        return $this->expectedMin;
    }

    public function setExpectedMin(?float $expectedMin): static
    {
        $this->expectedMin = $expectedMin;
        return $this;
    }

    public function getExpectedMax(): ?float
    {
        return $this->expectedMax;
    }

    public function setExpectedMax(?float $expectedMax): static
    {
        $this->expectedMax = $expectedMax;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTimeImmutable $resolvedAt): static
    {
        $this->resolvedAt = $resolvedAt;
        return $this;
    }

    /**
     * Check if the alert is resolved
     */
    #[Groups(['alert:read'])]
    public function isResolved(): bool
    {
        return $this->resolvedAt !== null;
    }

    /**
     * Mark the alert as resolved
     */
    public function resolve(): static
    {
        $this->resolvedAt = new \DateTimeImmutable();
        return $this;
    }
}
