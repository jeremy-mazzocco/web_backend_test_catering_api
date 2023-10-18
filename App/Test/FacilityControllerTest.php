<?php

namespace App\Test;

use PHPUnit\Framework\TestCase;

use App\Controllers\FacilityController;
use App\Plugins\Db\Db;
use App\Services\FacilityService;
use App\Plugins\Http\Request;
use App\Plugins\Http\Exceptions;
use App\Plugins\Http\Response as Status;


class FacilityControllerTest extends TestCase
{
    private $controller;
    private $facilityServiceMock;
    private $dbMock;


    protected function setUp(): void
    {

        $this->facilityServiceMock = $this->createMock(FacilityService::class);
        $this->controller = new FacilityController($this->facilityServiceMock);
        $this->dbMock = $this->createMock(Db::class);
    }

    // GET FACILITY BY ID
    public function testGetFacilityByIdValidId()
    {

        $facilityId = 2;

        $response = $this->controller->getFacilityById($facilityId);

        $this->assertInstanceOf(Status\Ok::class, $response);
        $this->assertArrayHasKey('id', $response->data);
        $this->assertArrayHasKey('name', $response->data);
        $this->assertArrayHasKey('tags', $response->data);
        $this->assertArrayHasKey('employees', $response->data);
        $this->assertEquals($facilityId, $response->data['id']);
    }

    public function testGetFacilityByIdInvalidId()
    {
        $facilityId = 'invalid';

        $this->expectException(Exceptions\BadRequest::class);

        $this->controller->getFacilityById($facilityId);
    }

    public function testGetFacilityByIdNonexistentId()
    {
        $facilityId = 9999;

        $this->expectException(Exceptions\NotFound::class);

        $this->controller->getFacilityById($facilityId);
    }

    // CREATE

    public function testCreateFacilityValidData()
    {
        $validData = [
            'name' => 'New Facility',
            'creation_date' => '2023-10-18',
            'location_id' => 1,
            'tags' => ['Tag1', 'Tag2']
        ];


        $request = new Request(json_encode($validData));
        $this->controller->setRequest($request);

        $this->facilityServiceMock->expects($this->once())
            ->method('create')
            ->with($validData);

        $this->dbMock->expects($this->once())
            ->method('beginTransaction');

        $this->dbMock->expects($this->once())
            ->method('commit');

        $response = $this->controller->createFacility();
        $this->assertInstanceOf(Status\Created::class, $response);
        $this->assertEquals('Facility created successfully!', $response->getBody()['message']);
    }

    public function testCreateFacilityInvalidData()
    {
     
        $invalidData = [
            'name' => 'New Facility',
            'creation_date' => '2023-10-18',
            'location_id' => 1,
            'tags' => ['Tag1', 'Tag2']
        ];

        
        $request = new Request(json_encode($invalidData));
        $this->controller->setRequest($request);

        $this->expectException(Exceptions\BadRequest::class);

        
        $this->dbMock->expects($this->never())
            ->method('beginTransaction');

        $this->dbMock->expects($this->never())
            ->method('commit');

        $this->facilityServiceMock->expects($this->never())
            ->method('create');

        $this->dbMock->expects($this->once())
            ->method('rollBack');

        $this->controller->createFacility();
    }













    // ... Other test cases for error scenarios ...







    protected function tearDown(): void
    {
        $this->controller = null;
        $this->facilityServiceMock = null;
        $this->dbMock = null;
    }
}
