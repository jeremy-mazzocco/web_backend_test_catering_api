<?php

namespace App\Services;

use App\Models\Employee;
use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


class EmployeeService extends Injectable
{
    /**
     * Fetches an employee by their ID.
     *
     * @param int $employeeId The ID of the employee.
     * @return array The employee data.
     */
    public function getByID($employeeId)
    {
        $employee = $this->getEmployeeData($employeeId);

        return $employee;
    }

    /**
     * Creates a new employee record in the database.
     *
     * @param array $data The employee data to be inserted.
     * @throws Exceptions\InternalServerError If insertion fails.
     */
    public function create($data)
    {
        // check if email is already in database
        $this->checkEmailExistence($data);

        // check if facility is already in database
        $this->doesFacilityExist($data['facility_id']);

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

    /**
     * Edits an existing employee record in the database.
     *
     * @param array $data The updated employee data.
     * @param int $employeeId The ID of the employee to be updated.
     * @throws Exceptions\InternalServerError If update fails.
     */
    public function edit($data, $employeeId)
    {
        // check if facility is already in database
        $this->doesEmployeeExist($employeeId);

        // check if email is already in database
        $this->checkEmailExistence($data);

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

    /**
     * Deletes an employee record from the database.
     *
     * @param int $employeeId The ID of the employee to be deleted.
     * @throws Exceptions\InternalServerError If deletion fails.
     */
    public function delete($employeeId)
    {

        // check if employee is already in database
        $this->doesEmployeeExist($employeeId);

        $query = 'DELETE FROM Employee WHERE id = :employee_id';
        $bind = ['employee_id' => $employeeId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Error deleting associated employees.']);
        }
    }


    // OTHER FUNCTIONS: 

    /**
     * Checks if the provided email already exists in the Employee table.
     *
     * @param array $employee The employee data containing the email.
     * @throws Exceptions\InternalServerError If there's an issue executing the query.
     * @throws Exceptions\BadRequest If the email already exists in the database.
     */
    public function checkEmailExistence($employee)
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

    /**
     * Verifies the existence of a facility by its ID.
     *
     * @param int $facilityId The ID of the facility to verify.
     * @throws Exceptions\InternalServerError If there's an issue executing the query.
     * @throws Exceptions\NotFound If the facility is not found in the database.
     */
    public function doesFacilityExist($facilityId)
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

    /**
     * Verifies the existence of an employee by their ID.
     *
     * @param int $employeeId The ID of the employee to verify.
     * @throws Exceptions\InternalServerError If there's an issue executing the query.
     * @throws Exceptions\NotFound If the employee is not found in the database.
     */
    public function doesEmployeeExist($employeeId)
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

    /**
     * Fetches data for an employee by their ID.
     *
     * @param int $employeeId The ID of the employee.
     * @return array The data of the requested employee.
     * @throws Exceptions\InternalServerError If there's an issue executing the query.
     * @throws Exceptions\NotFound If the employee data is not found in the database.
     */
    public function getEmployeeData($employeeId)
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
