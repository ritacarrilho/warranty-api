<?php

namespace App\Service;

use App\Entity\Warranty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ManufacturerService
{
    public function checkAndDeleteManufacturer(Warranty $warranty): void
    {
        $manufacturer = true;
    }
}