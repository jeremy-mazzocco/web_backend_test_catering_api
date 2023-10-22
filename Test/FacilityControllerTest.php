<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\FacilityController;
use App\Services\FacilityService;

class FacilityControllerTest extends TestCase
{
    private $facilityController;
    private $facilityServiceMock;

    protected function setUp(): void
    {
        $this->facilityServiceMock = $this->createMock(FacilityService::class);
        // $this->facilityController = new FacilityController;
    }

   
    

}
