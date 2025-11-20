<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\MeasurementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MeasurementRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['measurement:read']]),
        new GetCollection(normalizationContext: ['groups' => ['measurement:read']]),
        new Post(
            denormalizationContext: ['groups' => ['measurement:write']],
            securityPostDenormalize: "is_granted('ROLE_USER')"
        ),
        new Put(
            denormalizationContext: ['groups' => ['measurement:write']],
            security: "is_granted('ROLE_USER')"
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
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
    #[Groups(['measurement:read', 'measurement:write'])]
    #[Assert\NotNull]
    private ?Reservoir $reservoir = null;

    #[ORM\Column]
    #[Groups(['measurement:read', 'measurement:write'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $measuredAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['measurement:read', 'measurement:write'])]
    private ?float $ph = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['measurement:read', 'measurement:write'])]
    private ?float $ec = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['measurement:read', 'measurement:write'])]
    private ?float $waterTemp = null;

    #[ORM\Column(length: 50)]
    #[Groups(['measurement:read', 'measurement:write'])]
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
