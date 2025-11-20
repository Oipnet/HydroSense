<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CultureProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CultureProfile - Référentiel des profils de cultures hydroponiques
 * 
 * Contient les plages idéales (pH, EC, température) pour différentes cultures.
 * Version V1 : lecture seule pour l'analyse et les recommandations.
 */
#[ORM\Entity(repositoryClass: CultureProfileRepository::class)]
#[ORM\Table(name: 'culture_profile')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 30
)]
class CultureProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * Nom de la culture (ex: "Laitue", "Basilic", "Fraises")
     */
    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le nom de la culture est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $name = null;

    /**
     * pH minimum recommandé
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull(message: 'Le pH minimum est obligatoire.')]
    #[Assert\Range(
        min: 0,
        max: 14,
        notInRangeMessage: 'Le pH doit être entre {{ min }} et {{ max }}.'
    )]
    private ?float $phMin = null;

    /**
     * pH maximum recommandé
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull(message: 'Le pH maximum est obligatoire.')]
    #[Assert\Range(
        min: 0,
        max: 14,
        notInRangeMessage: 'Le pH doit être entre {{ min }} et {{ max }}.'
    )]
    private ?float $phMax = null;

    /**
     * Électroconductivité (EC) minimum en mS/cm
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull(message: 'L\'EC minimum est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'L\'EC doit être positif ou zéro.')]
    private ?float $ecMin = null;

    /**
     * Électroconductivité (EC) maximum en mS/cm
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull(message: 'L\'EC maximum est obligatoire.')]
    #[Assert\Positive(message: 'L\'EC maximum doit être positif.')]
    private ?float $ecMax = null;

    /**
     * Température de l'eau minimum en °C
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull(message: 'La température minimum est obligatoire.')]
    #[Assert\Range(
        min: 0,
        max: 50,
        notInRangeMessage: 'La température doit être entre {{ min }}°C et {{ max }}°C.'
    )]
    private ?float $waterTempMin = null;

    /**
     * Température de l'eau maximum en °C
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull(message: 'La température maximum est obligatoire.')]
    #[Assert\Range(
        min: 0,
        max: 50,
        notInRangeMessage: 'La température doit être entre {{ min }}°C et {{ max }}°C.'
    )]
    private ?float $waterTempMax = null;

    // Getters et Setters

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

    public function getPhMin(): ?float
    {
        return $this->phMin;
    }

    public function setPhMin(float $phMin): static
    {
        $this->phMin = $phMin;
        return $this;
    }

    public function getPhMax(): ?float
    {
        return $this->phMax;
    }

    public function setPhMax(float $phMax): static
    {
        $this->phMax = $phMax;
        return $this;
    }

    public function getEcMin(): ?float
    {
        return $this->ecMin;
    }

    public function setEcMin(float $ecMin): static
    {
        $this->ecMin = $ecMin;
        return $this;
    }

    public function getEcMax(): ?float
    {
        return $this->ecMax;
    }

    public function setEcMax(float $ecMax): static
    {
        $this->ecMax = $ecMax;
        return $this;
    }

    public function getWaterTempMin(): ?float
    {
        return $this->waterTempMin;
    }

    public function setWaterTempMin(float $waterTempMin): static
    {
        $this->waterTempMin = $waterTempMin;
        return $this;
    }

    public function getWaterTempMax(): ?float
    {
        return $this->waterTempMax;
    }

    public function setWaterTempMax(float $waterTempMax): static
    {
        $this->waterTempMax = $waterTempMax;
        return $this;
    }
}
