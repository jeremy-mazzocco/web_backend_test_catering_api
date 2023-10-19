<?php

namespace Test;

use PHPUnit\Framework\TestCase;

use App\Plugins\Db\Db;
use App\Services\FacilityService;
use App\Plugins\Http\Request;
use App\Plugins\Http\Exceptions;
use App\Plugins\Http\Response as Status;


class FacilityServiceTest extends TestCase
{
    private $facilityService;
    private $dbMock;

    protected function setUp(): void
    {
        // Monk database
        $this->dbMock = $this->getMockBuilder(Db::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock the executeQuery method
        $this->dbMock->method('executeQuery')
            ->willReturn(true);

        $this->facilityService = new FacilityService();
        $this->facilityService->db = $this->dbMock;
    }


    public function testGetByID()
    {

        $facilityId = 1;

        $facilityMockData = [
            [
                'id' => 1,
                'name' => 'Amsterdam Riverside Venue',
                'creation_date' => '2019-05-10',
                'location_id' => 1
            ]
        ];

        $this->dbMock->method('getResults')
            ->willReturn($facilityMockData);


        $result = $this->facilityService->getByID($facilityId);

        // Assert that the result meets your expectations
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Amsterdam Riverside Venue', $result['name']);
        $this->assertEquals('2019-05-10', $result['creation_date']);
        $this->assertEquals(1, $result['location_id']);
    }


    // OTHER FUNCTIONS

    public function testFetchDataLocationById()
    {
        $locationId = 1;

        $locationMockData = [
            'id' => 1,
            'city' => 'Amsterdam',
            'address' => 'Dam Square',
            'zip_code' => '1012 JL',
            'country_code' => 'NL',
            'phone_number' => '+31-20-555-1234'
        ];

        $this->dbMock->method('getResults')->willReturn([$locationMockData]);

        $result = $this->facilityService->fetchDataLocationById($locationId);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Amsterdam', $result['city']);
        $this->assertEquals('Dam Square', $result['address']);
        $this->assertEquals('1012 JL', $result['zip_code']);
        $this->assertEquals('NL', $result['country_code']);
        $this->assertEquals('+31-20-555-1234', $result['phone_number']);
    }

    public function testFetchDataTagsById()
    {

        $facilityId = 1;

        $tagMockData = [
            'name' => 'Private Parties',
        ];

        $this->dbMock->method('getResults')->willReturn([$tagMockData]);

        $facility = ['id' => $facilityId];
        $this->facilityService->fetchDataTagsById($facility);

        $this->assertEquals(['Private Parties'], $facility['tags']);
    }

    public function testFetchDataEmployee()
    {
        $facilityId = 1;

        $employeeMockData = [
            "id" => 6,
            "first_name" => "Harry",
            "last_name" => "Potter",
            "role" => "Magician",
            "facility_id" => 1,
            "email" => "harry.potter@example.com"
        ];
        
        $this->dbMock->method('getResults')->willReturn([$employeeMockData]);

        $facility = ['id' => $facilityId];
        $this->facilityService->fetchDataEmployee($facility);

        $this->assertEquals([$employeeMockData], $facility['employees']);
    }
}
