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
            // check if email is already in database
            $this->isEmailRegistered($data);

            // check if facility is already in database
            $this->isFacilityExist($data['facility_id']);

            $employee = new Employee(
                $data['first_name'],
                $data['last_name'],
                $data['role'],
                $data['facility_id'],
                $data['email']
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

    public function edit($data, $employeeId)
    {
        // check if facility is already in database
        $this->isEmployeeExist($employeeId);

        // check if email is already in database
        $this->isEmailRegistered($data);

        $employee = new Employee(
            $data['first_name'],
            $data['last_name'],
            $data['role'],
            $data['facility_id'],
            $data['email']
        );

        // Update the existing employee
        $query = 'UPDATE Employee SET first_name = :first_name, last_name = :last_name, role = :role, facility_id = :facility_id, email = :email WHERE id = :id';
        $bind = [
            'first_name' => $employee->getFirstName(),
            'last_name' => $employee->getLastName(),
            'role' => $employee->getRole(),
            'facility_id' => $employee->getFacilityId(),
            'email' => $employee->getEmail(),
            'id' => $employeeId
        ];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to update employee.']);
        }
    }

    public function delete($employeeId)
    {

        // check if employee is already in database
        $this->isEmployeeExist($employeeId);

        $query = 'DELETE FROM Employee WHERE id = :employee_id';
        $bind = ['employee_id' => $employeeId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Error deleting associated employees.']);
        }
    }



    // OTHER FUNCTIONS:
    public function isEmailRegistered($employee)
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

    public function isFacilityExist($facilityId)
    {
        // Check if the facility exists
        $query = 'SELECT id FROM Facility WHERE id = :facility_id';
        $bind = ['facility_id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to execute query during facility verification.']);
        }
        if (!$this->db->getResults()) {
            throw new Exceptions\NotFound(['message' => 'Not Found. Facility does not exist.']);
        }
    }

    public function isEmployeeExist($employeeId)
    {
        // Check if the facility exists
        $query = 'SELECT id FROM Employee WHERE id = :employee_id';
        $bind = ['employee_id' => $employeeId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to execute query during facility verification.']);
        }
        if (!$this->db->getResults()) {
            throw new Exceptions\NotFound(['message' => 'Not Found. Employee does not exist.']);
        }
    }

    public function fetchDataEmployee($employeeId)

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
