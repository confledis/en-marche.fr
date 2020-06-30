<?php

namespace App\Entity\Geo;

use Doctrine\ORM\Mapping as ORM;

trait UnderDepartmentTrait
{
    /**
     * @var Department
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Department")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function setDepartment(Department $department): void
    {
        $this->department = $department;
    }
}
