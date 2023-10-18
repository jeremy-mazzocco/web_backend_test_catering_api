<?php

namespace App\Services;

use App\Models\Employee;
use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


class EmployeeService extends Injectable
{
    public function getByID($employeeId)
    {
        $employee = $this->fetchDataEmployee($employeeId);

        return $employee;
    }

    public function create($data)
    {
        foreach ($data['employees'] as $employee) {

            // check if employee is already in database
            $this->selectEmployeeByEmail($employee);

            // check if facility exsist in database
            $this->selectFacilityById($employee['facility_id']);

            $employee = new Employee(
                $employee['first_name'],
                $employee['last_name'],
                $employee['role'],
                $employee['facility_id'],
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

    public function edit($data)
    {
        // check if employee is already in database
        $this->selectEmployeeByEmail($data);

        // check if facility exsist in database
        $this->selectFacilityById($data['facility_id']);

        $employee = new Employee(
            $data['first_name'],
            $data['last_name'],
            $data['role'],
            $data['facility_id'],
            $data['email']
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

    public function delete($employeeId)
    {

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

        return $results;
    }



    // OTHER FUNCTIONS:
    private function selectEmployeeByEmail(&$employee)
    {
        $emailQuery = 'SELECT id FROM Employee WHERE email = :email';
        $emailBind = ['email' => $employee['email']];

        if (!$this->db->executeQuery($emailQuery, $emailBind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to execute query during employee email verification.']);
        }

        if ($this->db->getResults()) {
            throw new Exceptions\BadRequest(['message' => 'Employee email in database.']);
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

    private function fetchDataEmployee($employeeId)

    {
        $query = 'SELECT * FROM Employee WHERE id = :id';
        $bind = ['id' => $employeeId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Error fetching employees from the database.']);
        }
        if (!$employee = $this->db->getResults()) {
            throw new Exceptions\NotFound(['message' => 'Not Found. Employee not in database.']);
        }
        return $employee[0];
    }
}
