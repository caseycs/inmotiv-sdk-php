<?php
declare(strict_types=1);

namespace InMotivClient\Container;

class VehicleInfoContainer
{
    const CLASS_MOTORCYCLE = 12;
    const CLASS_MOTORCYCLE_WITH_SIDECAR = 13;

    /** @var string */
    private $brand;

    /** @var int */
    private $productionYear;

    /** @var int */
    private $engineCC;

    /** @var int */
    private $horsePower;

    /** @var int */
    private $weight;

    /** @var int */
    private $catalogPrice;

    /** @var bool */
    private $isStolen;

    /** @var string */
    private $rawResponse;

    public function __construct(
        string $brand,
        ?int $productionYear,
        ?int $engineCC,
        int $horsePower,
        int $weight,
        ?int $catalogPrice,
        int $rdwClass,
        bool $isStolen,
        string $rawResponse
    ) {
        $this->brand = $brand;
        $this->productionYear = $productionYear;
        $this->engineCC = $engineCC;
        $this->horsePower = $horsePower;
        $this->weight = $weight;
        $this->catalogPrice = $catalogPrice;
        $this->rdwClass = $rdwClass;
        $this->isStolen = $isStolen;
        $this->rawResponse = $rawResponse;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getProductionYear(): ?int
    {
        return $this->productionYear;
    }

    public function getEngineCC(): ?int
    {
        return $this->engineCC;
    }

    public function getHorsePower(): int
    {
        return $this->horsePower;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getCatalogPrice(): ?int
    {
        return $this->catalogPrice;
    }

    public function isMotorcycle(): bool
    {
        return $this->rdwClass === self::CLASS_MOTORCYCLE || $this->rdwClass === self::CLASS_MOTORCYCLE_WITH_SIDECAR;
    }

    public function isStolen(): bool
    {
        return $this->isStolen;
    }

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }
}
