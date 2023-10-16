<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Facility;
use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


class FacilityService extends Injectable
{

    public function AllFacilities($pagination)
    {

        // Fetch all facility
        $query = 'SELECT * FROM Facility LIMIT ' . $pagination['limit'] . ' OFFSET ' . $pagination['offset'];

        if (!$this->db->executeQuery($query)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Error fetching facilities from the database.']);
        }
        if (!$facilities = $this->db->getResults()) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Error fetching facilities from the database.']);
        }


        // For each facility, attach associated location, tags and employee
        foreach ($facilities as &$facility) {

            $this->fetchDataLocationById($facility);

            $this->fetchDataTagsById($facility);

            $this->fetchDataEmployee($facility);
        }

        return $facilities;
    }

    public function getByID($facilityId)
    {

        // Get the facility from the database by ID
        $query = 'SELECT * FROM Facility WHERE id = :id';
        $bind = ['id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Failed to retrieve the facility.']);
        }
        if (!$facility = $this->db->getResults()) {
            throw new Exceptions\NotFound(['Message' => 'Not Found. No facility found with the provided ID.']);
        }
        $facility = $facility[0];


        $this->fetchDataLocationById($facility);

        $this->fetchDataTagsById($facility);

        $this->fetchDataEmployee($facility);

        return $facility;
    }

    public function create($data)
    {

        $facility = new Facility(
            $data['name'],
            $data['creation_date'],
            $data['location_id']
        );


        // Check if a facility with the same name and location already exists
        $query = 'SELECT id FROM Facility WHERE name = :name AND location_id = :location_id';
        $bind = [
            'name' => $facility->getName(),
            'location_id' => $facility->getLocationId()
        ];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to execute query during facility verification.']);
        }
        if ($existingFacility = $this->db->getResults()) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. A facility with this name already exists at the specified location.']);
        }


        // Insert the facility
        $query = 'INSERT INTO Facility (name, creation_date, location_id) VALUES (:name, :creation_date, :location_id)';
        $bind = [
            'name' => $facility->getName(),
            'creation_date' => $facility->getCreationDate(),
            'location_id' => $facility->getLocationId()
        ];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to create new facility.']);
        }

        $facilityId = $this->db->getLastInsertedId();


        // Insert tags
        foreach ($data['tags'] as $tag) {

            $this->createTag($tag, $facilityId);
        }


        // Insert new employees
        if (isset($data['employees'])) {
            foreach ($data['employees'] as $employee) {

                $this->selectEmployeeByEmail($employee);

                $employee = new Employee(
                    $employee['first_name'],
                    $employee['last_name'],
                    $employee['role'],
                    $facilityId,
                    $employee['email']
                );

                $query = 'INSERT INTO Employee (first_name, last_name, role, facility_id, email) VALUES (:first_name, :last_name, :role, :facility_id, :email)';
                $bind = [
                    'first_name' => $employee->getFirstName(),
                    'last_name' => $employee->getLastName(),
                    'role' => $employee->getRole(),
                    'facility_id' => $employee->getFacilityId(),
                    'email' => $employee->getEmail()
                ];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to create new employee.']);
                }
            }
        }
    }

    public function edit($facilityId, $data)
    {

        $facility = new Facility(
            $data['name'],
            $data['creation_date'],
            $data['location_id'],
            $data['tags']
        );


        $this->selectFacilityById($facilityId);


        // Update the facility into the database
        $query = 'UPDATE Facility SET name = :name, creation_date = :creation_date, location_id = :location_id WHERE id = :facility_id';
        $bind = [
            'name' => $facility->getName(),
            'creation_date' => $facility->getCreationDate(),
            'location_id' => $facility->getLocationId(),
            'facility_id' => $facilityId
        ];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to update facility.']);
        }


        $this->deleteFacilityTags($facilityId);


        // Insert new tags
        foreach ($data['tags'] as $tag) {

            $this->createTag($tag, $facilityId);
        }


        // Update employee
        if (isset($data['employees'])) {

            foreach ($data['employees'] as $employeeData) {

                $this->selectEmployeeByEmail($employeeData);

                $employee = new Employee(
                    $employeeData['first_name'],
                    $employeeData['last_name'],
                    $employeeData['role'],
                    $facilityId,
                    $employeeData['email']
                );

                // Update the existing employee
                $query = 'UPDATE Employee SET first_name = :first_name, last_name = :last_name, role = :role, facility_id = :facility_id WHERE email = :email';
                $bind = [
                    'first_name' => $employee->getFirstName(),
                    'last_name' => $employee->getLastName(),
                    'role' => $employee->getRole(),
                    'facility_id' => $employee->getFacilityId(),
                    'email' => $employee->getEmail()
                ];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to update employee.']);
                }
            }
        }
    }

    public function delete($facilityId)
    {

        $this->selectFacilityById($facilityId);

        // Delete the employees associated with the facility
        $query = 'DELETE FROM Employee WHERE facility_id = :facility_id';
        $bind = ['facility_id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Error deleting associated employees.']);
        }

        $this->deleteFacilityTags($facilityId);

        // Delete the facility
        $query = 'DELETE FROM Facility WHERE id = :id';
        $bind = ['id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Error deleting the facility.']);
        }
    }

    // public function createEmpl($data)
    // {
    //        // Check if the email is already in use
    //        $emailQuery = 'SELECT id FROM Employee WHERE email = :email';
    //        $emailBind = ['email' => $data['email']];
   
    //        if (!$this->db->executeQuery($emailQuery, $emailBind)) {
    //            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to execute query during employee email verification.']);
    //        }
   
    //        if ($this->db->getResults()) {
    //            throw new Exceptions\BadRequest(['message' => 'Employee email already in database.']);
    //        } else {

    //         // Crate a new employee asociate with facilty_id

    //        }


    // }

    public function deleteEmpl($employeeId)
    {

        // Delete the employee
        $query = 'DELETE FROM Employee WHERE id = :employee_id';
        $bind = ['employee_id' => $employeeId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Error deleting associated employees.']);
        }
    }

    public function search($pagination, $data)
    {
        // Query to join all tables
        $query = "SELECT DISTINCT Facility.* FROM Facility 
                    LEFT JOIN Facility_Tag ON Facility.id = Facility_Tag.facility_id
                    LEFT JOIN Tag ON Facility_Tag.tag_id = Tag.id
                    LEFT JOIN Location ON Facility.location_id = Location.id";

        $bind = [];
        $conditions = [];

        // add facility to the conditions
        if (!empty($data['name'])) {
            $conditions[] = "Facility.name LIKE :name";
            $bind['name'] = '%' . $data['name'] . '%';
        }

        // add tag to the conditions
        if (!empty($data['tags'])) {
            $conditions[] = "Tag.name LIKE :tags";
            $bind['tags'] = '%' . $data['tags'] . '%';
        }

        // add location to the conditions
        if (!empty($data['location'])) {
            $conditions[] = "Location.city LIKE :location";
            $bind['location'] = '%' . $data['location'] . '%';
        }


        // Add pagination to the query
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' LIMIT ' . $pagination['limit'] . ' OFFSET ' . $pagination['offset'];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => "Internal Server Error. Failed to execute the search query."]);
        }
        if (!$results = $this->db->getResults()) {
            throw new Exceptions\BadRequest(['Message' => "Bad Request. No facilities found with the provided search criteria."]);
        }


        // Associate the tags to the facility
        foreach ($results as &$facility) {
            $facilityId = $facility['id'];
            $tagQuery = 'SELECT Tag.name FROM Tag
                             INNER JOIN Facility_Tag ON Tag.id = Facility_Tag.tag_id
                             WHERE Facility_Tag.facility_id = :facility_id';
            $tagBind = ['facility_id' => $facilityId];

            if ($this->db->executeQuery($tagQuery, $tagBind)) {
                $tags = $this->db->getResults();
                $facility['tags'] = array_column($tags, 'name');
            } else {
                throw new Exceptions\InternalServerError(['Message' => "Internal Server Error. Failed to retrieve tags for the facility."]);
            }
        }

        return $facility;
    }



    // OTHER FUNCTIONS:

    private function fetchDataLocationById($facility)
    {
        $query = 'SELECT * FROM Location WHERE id = :location_id';
        $bind = ['location_id' => $facility['location_id']];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Failed to retrieve the location for the facility.']);
        }
        if (!$location = $this->db->getResults()) {
            throw new Exceptions\NotFound(['Message' => 'Not Found. Location associated with the facility not found.']);
        }
        $facility['location'] = $location[0];
    }

    private function fetchDataTagsById(&$facility)
    {
        $facilityId = $facility['id'];

        $query = 'SELECT * FROM Tag
            JOIN Facility_Tag ON Facility_Tag.tag_id = Tag.id
            WHERE Facility_Tag.facility_id = :facility_id';
        $bind = ['facility_id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Failed to retrieve tags for the facility.']);
        }
        $tags = $this->db->getResults();

        foreach ($tags as $tag) {
            $facility['tags'][] = $tag['name'];
        };
    }

    private function fetchDataEmployee(&$facility)
    {
        $query = 'SELECT * FROM Employee WHERE facility_id = :id';
        $bind = ['id' => $facility['id']];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Error fetching employees from the database.']);
        }
        $employees = $this->db->getResults();

        $facility['employees'] = $employees;
    }

    private function createTag(&$tag, $facilityId)
    {
        // select tag by name
        $query = 'SELECT id FROM Tag WHERE name = :name';
        $bind = ['name' => $tag];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed during tag existence check.']);
        }

        // insert tag if not exist
        if (!$existingTag = $this->db->getResults()) {
            $query = 'INSERT INTO Tag (name) VALUES (:name)';
            $bind = ['name' => $tag];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to create new tag.']);
            }

            $tagId = $this->db->getLastInsertedId();
        } else {

            // take id if exist
            $tagId = $existingTag[0]['id'];
        }

        // Create the association between the facility and the tag
        $query = 'INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
        $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to associate tag with facility.']);
        }
    }

    private function selectEmployeeByEmail(&$employee)
    {
        $emailQuery = 'SELECT id FROM Employee WHERE email = :email';
        $emailBind = ['email' => $employee['email']];

        if (!$this->db->executeQuery($emailQuery, $emailBind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to execute query during employee email verification.']);
        }

        if (!$this->db->getResults()) {
            throw new Exceptions\BadRequest(['message' => 'Employee email not in database.']);
        }
    }

    private function selectFacilityById(&$facilityId)
    {
        // Check if the facility exists
        $query = 'SELECT id FROM Facility WHERE id = :facility_id';
        $bind = ['facility_id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to execute query during facility verification.']);
        }
        if (!$existingFacility = $this->db->getResults()) {
            throw new Exceptions\NotFound(['message' => 'Not Found. Facility does not exist.']);
        }
    }

    private function deleteFacilityTags(&$facilityId)
    {
        $query = 'DELETE FROM Facility_Tag WHERE facility_id = :facility_id';
        $bind = ['facility_id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to remove existing tag associations.']);
        }
    }
}
