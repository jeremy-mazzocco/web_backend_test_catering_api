<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use App\Models\Facility;
use App\Plugins\Db\Db;
use App\Services\FacilityService;


class FacilityServiceTest extends TestCase
{
    private $facilityService;
    private $dbMock;

    protected function setUp(): void
    {
        $this->dbMock = $this->getMockBuilder(Db::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->facilityService = new FacilityService;

        $this->facilityService->db = $this->dbMock;
    }

    public function testAllFacilities()
    {
        $pagination = [
            'limit' => 8,
            'offset' => 0,
        ];

        $facilityMockData = [
            [
                'id' => 1,
                'name' => 'Amsterdam Riverside Venue',
                'creation_date' => '2019-05-10',
                'location_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'Rotterdam Skyline Hall',
                'creation_date' => '2017-07-07',
                'location_id' => 2
            ],
            [
                'id' => 3,
                'name' => 'Utrecht Medieval Hall',
                'creation_date' => '2018-09-15',
                'location_id' => 3
            ],
            [
                'id' => 4,
                'name' => 'Eindhoven Modern Lounge',
                'creation_date' => '2020-01-20',
                'location_id' => 4
            ],
            [
                'id' => 5,
                'name' => 'The Hague Royal Banquet',
                'creation_date' => '2016-03-30',
                'location_id' => 5
            ],
            [
                'id' => 6,
                'name' => 'Groningen Market Venue',
                'creation_date' => '2021-08-08',
                'location_id' => 6
            ],
            [
                'id' => 7,
                'name' => 'Maastricht Classic Venue',
                'creation_date' => '2019-10-10',
                'location_id' => 7
            ],
            [
                'id' => 8,
                'name' => 'Amsterdam Floating Venue',
                'creation_date' => '2022-04-04',
                'location_id' => 1
            ]
        ];

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn($facilityMockData);

        $result = $this->facilityService->AllFacilities($pagination);

        $this->assertIsArray($result);
        $this->assertCount(count($facilityMockData), $result);
        $this->assertEquals($facilityMockData[0]['id'], $result[0]['id']);
        $this->assertEquals($facilityMockData[0]['name'], $result[0]['name']);
        $this->assertEquals($facilityMockData[0]['creation_date'], $result[0]['creation_date']);
        $this->assertEquals($facilityMockData[0]['location_id'], $result[0]['location_id']);
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

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn($facilityMockData);

        $result = $this->facilityService->getByID($facilityId);

        $this->assertIsArray($result);
        $this->assertEquals($facilityId, $result['id']);
        $this->assertEquals($facilityMockData[0]['name'], $result['name']);
        $this->assertEquals($facilityMockData[0]['creation_date'], $result['creation_date']);
        $this->assertEquals($facilityMockData[0]['location_id'], $result['location_id']);
    }

    public function testCreate()
    {
        $data = [
            'name' => 'Amsterdam Create Venue',
            'creation_date' => '2019-05-10',
            'location_id' => 1,
        ];

        $this->facilityService->db->method('executeQuery')->willReturn(true);

        $facility = $this->facilityService->create($data);

        $this->assertInstanceOf(Facility::class, $facility);
        $this->assertEquals($data['name'], $facility->getName());
        $this->assertEquals($data['creation_date'], $facility->getCreationDate());
        $this->assertEquals($data['location_id'], $facility->getLocationId());
    }

    public function testEdit()
    {
        $facilityId = 1;
        $data = [
            'name' => 'Updated Venue Name',
            'creation_date' => '2022-01-01',
            'location_id' => 2,
            'tags' => ['Tag1', 'Tag2']
        ];

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn([['id' => $facilityId]]);

        $editedFacility = $this->facilityService->edit($facilityId, $data);

        $this->assertInstanceOf(Facility::class, $editedFacility);
        $this->assertEquals($data['name'], $editedFacility->getName());
        $this->assertEquals($data['creation_date'], $editedFacility->getCreationDate());
        $this->assertEquals($data['location_id'], $editedFacility->getLocationId());
    }

    public function testDelete()
    {
        $facilityId = 1;

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn([['id' => $facilityId]]);

        $this->facilityService->delete($facilityId);

        $this->expectNotToPerformAssertions();
    }


    // OTHER FUNCTIONS
    public function testFetchDataLocationById()
    {
        $facility = [
            'location_id' => 1,
        ];

        $locationMockData = [
            [
                'id' => 1,
                'city' => 'Amsterdam',
                'address' => '1 Dam Square',
                'zip_code' => '1012 JL',
                'country_code' => 'NL',
                'phone_number' => '+31-20-555-1234'
            ]
        ];

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn($locationMockData);

        $result = $this->facilityService->fetchDataLocationById($facility);

        $this->assertIsArray($result);
        $this->assertCount(count($locationMockData[0]), $result);
        $this->assertEquals($locationMockData[0]['id'], $result['id']);
        $this->assertEquals($locationMockData[0]['city'], $result['city']);
        $this->assertEquals($locationMockData[0]['address'], $result['address']);
        $this->assertEquals($locationMockData[0]['zip_code'], $result['zip_code']);
        $this->assertEquals($locationMockData[0]['country_code'], $result['country_code']);
        $this->assertEquals($locationMockData[0]['phone_number'], $result['phone_number']);
    }

    public function testFetchDataTagsById()
    {
        $facility = [
            'id' => 1,
            'tags' => []
        ];

        $tagsMonkData = [
            [
                'id' => 1,
                'name' => 'Weddings',
            ]
        ];

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn($tagsMonkData);

        $this->facilityService->fetchDataTagsById($facility);

        $this->assertIsArray($facility);
        $this->assertCount(count($tagsMonkData[0]), $facility);
        $this->assertEquals($tagsMonkData[0]['id'], $facility['id']);
        $this->assertEquals($tagsMonkData[0]['name'], $facility['tags'][0]);
    }

    public function testFetchDataEmployee()
    {
        $facility = [
            'id' => 6,
            'employees' => []
        ];

        $employeesData = [
            [
                'id' => 14,
                'first_name' => 'Fiona',
                'last_name' => 'Smith',
                'role' => 'Receptionist',
                'facility_id' => 6,
                'email' => 'fiona.smith@example.com'
            ],
            [
                'id' => 21,
                'first_name' => 'Emily',
                'last_name' => 'Wang',
                'role' => 'Host',
                'facility_id' => 6,
                'email' => 'emily.wang@example.com'
            ],
            [
                'id' => 42,
                'first_name' => 'Mason',
                'last_name' => 'Petrov',
                'role' => 'Host',
                'facility_id' => 6,
                'email' => 'mason.petrov@example.com'
            ]
        ];
        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn($employeesData);

        $this->facilityService->fetchDataEmployee($facility);
        $facility = $facility['employees'];

        $this->assertIsArray($facility);
        $this->assertCount(count($employeesData), $facility);
        $this->assertEquals($employeesData[0]['first_name'], $facility[0]['first_name']);
        $this->assertEquals($employeesData[0]['last_name'], $facility[0]['last_name']);
        $this->assertEquals($employeesData[0]['role'], $facility[0]['role']);
        $this->assertEquals($employeesData[0]['email'], $facility[0]['email']);
    }

    public function testCreateTag()
    {
        $tagName = 'New Tag';
        $facilityId = 1;

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn([]);

        $this->facilityService->createTag($tagName, $facilityId);

        $this->facilityService->db->expects($this->any(0))
            ->method('executeQuery')
            ->with('SELECT id FROM Tag WHERE name = :name', ['name' => $tagName]);

        $this->facilityService->db->expects($this->any(1))
            ->method('executeQuery')
            ->with('INSERT INTO Tag (name) VALUES (:name)', ['name' => $tagName]);

        $this->facilityService->db->expects($this->any(2))
            ->method('executeQuery')
            ->with('INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)', [
                'facility_id' => $facilityId,
                'tag_id' => $this->facilityService->db->getLastInsertedId()
            ]);
        $this->expectNotToPerformAssertions();
    }

    public function testIsFacilityExist()
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

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn($facilityMockData);

        $this->facilityService->isFacilityExist($facilityId);

        $this->expectNotToPerformAssertions();
    }

    public function testDeleteFacilityTags()
    {
        $facilityId = 1;

        $this->facilityService->db->method('executeQuery')->willReturn(true);

        $this->facilityService->deleteFacilityTags($facilityId);

        $this->expectNotToPerformAssertions();
    }

    public function testSearch()
    {
        $pagination = ['limit' => 8, 'offset' => 0];
        $data = [
            'name' => 'Venue',
            'tags' => 'Outdoor',
            'location' => 'Ams'
        ];

        $this->facilityService->db->method('executeQuery')->willReturn(true);
        $this->facilityService->db->method('getResults')->willReturn([
            [
                "id" => 8,
                "name" => "Amsterdam Floating Venue",
                "creation_date" => "2022-04-04",
                "location_id" => 1,
                "tags" => [
                    "Corporate Events",
                    "Outdoor"
                ]
            ],
            [
                "id" => 15,
                "name" => "Amsterdam Museum Venue",
                "creation_date" => "2017-05-05",
                "location_id" => 1,
                "tags" => [
                    "Outdoor"
                ]
            ],
            [
                "id" => 16,
                "name" => "Amsterdam Light Venue",
                "creation_date" => "2017-05-05",
                "location_id" => 1,
                "tags" => [
                    "Outdoor"
                ]
            ],
            [
                "id" => 17,
                "name" => "Amsterdam Alley Venue",
                "creation_date" => "2017-05-05",
                "location_id" => 1,
                "tags" => [
                    "Outdoor"
                ]
            ],
            [
                "id" => 18,
                "name" => "Amsterdam Toni Venue",
                "creation_date" => "2017-05-05",
                "location_id" => 1,
                "tags" => [
                    "Outdoor"
                ]
            ]
        ]);

        $results = $this->facilityService->search($pagination, $data);

        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('name', $result);
            $this->assertArrayHasKey('creation_date', $result);
            $this->assertArrayHasKey('location_id', $result);
            $this->assertIsArray($result['tags']);
        }
    }
}
