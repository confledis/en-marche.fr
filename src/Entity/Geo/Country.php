<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_country")
 * @ORM\HasLifecycleCallbacks
 *
 * @Algolia\Index(autoIndex=false)
 */
class Country
{
    use GeoTrait;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }
}
