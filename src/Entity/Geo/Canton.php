<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_canton")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Canton
{
    use GeoTrait;
    use UnderDepartmentTrait;

    public function __construct(string $code, string $name, Department $department)
    {
        $this->code = $code;
        $this->name = $name;
        $this->department = $department;
    }
}
