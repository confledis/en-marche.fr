<?php

namespace App\Entity\AdherentCharter;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="adherent_charter", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"adherent_id", "dtype"})
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @Algolia\Index(autoIndex=false)
 */
abstract class AbstractAdherentCharter implements AdherentCharterInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime")
     */
    private $acceptedAt;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="charters")
     */
    private $adherent;

    public function __construct()
    {
        $this->acceptedAt = new \DateTime();
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
