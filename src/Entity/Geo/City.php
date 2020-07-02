<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_city")
 * @ORM\HasLifecycleCallbacks
 *
 * @Algolia\Index(autoIndex=false)
 */
class City
{
    use GeoTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     */
    private $population;

    /**
     * @var Department|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Department")
     * @ORM\JoinColumn(nullable=true)
     */
    private $department;

    /**
     * @var Canton|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Canton")
     * @ORM\JoinColumn(nullable=true)
     */
    private $canton;

    /**
     * @var CityCommunity|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\CityCommunity")
     * @ORM\JoinColumn(nullable=true)
     */
    private $cityCommunity;

    /**
     * @var District|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\District")
     * @ORM\JoinColumn(nullable=true)
     */
    private $district;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    public function getCanton(): ?Canton
    {
        return $this->canton;
    }

    public function setCanton(?Canton $canton): void
    {
        $this->canton = $canton;
    }

    public function getCityCommunity(): ?CityCommunity
    {
        return $this->cityCommunity;
    }

    public function setCityCommunity(?CityCommunity $cityCommunity): void
    {
        $this->cityCommunity = $cityCommunity;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): void
    {
        $this->district = $district;
    }
}
