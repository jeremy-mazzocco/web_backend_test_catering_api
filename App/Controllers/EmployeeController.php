<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Services\EmployeeService;


try {

    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
        class EmployeeController extends BaseController
        {
            /**
             * Retrieves an employee by their ID.
             *
             * @param int $employeeId The ID of the employee to retrieve.
             */
            public function getEmployeeById($employeeId)
            {
                try {

                    $this->validateEmployeeId($employeeId);

                    $facilityEmployee  = new EmployeeService();

                    $results = $facilityEmployee->getByID($employeeId);

                    (new Status\Ok($results))->send();
                } catch (Exceptions\InternalServerError $e) {

                    (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
                } catch (Exceptions\NotFound $e) {

                    (new Status\NotFound(['message' => $e->getMessage()]))->send();
                } catch (Exceptions\BadRequest $e) {

                    (new Status\BadRequest(['message' => $e->getMessage()]))->send();
                }
            }


            /**
             * Creates a new employee.
             */
            public function createEmployee()
            {
                try {

                    $this->db->beginTransaction();

                    $data = json_decode(file_get_contents("php://input"), true);

                    $this->validateEmployeeInputData($data);

                    $facilityEmployee  = new EmployeeService();

                    $facilityEmployee->create($data);

                    $this->db->commit();


                    (new Status\Created(['message' => 'Employee created successfully!']))->send();
                } catch (Exceptions\BadRequest $e) {

                    $this->db->rollBack();
                    (new Status\BadRequest(['message' => $e->getMessage()]))->send();
                } catch (Exceptions\InternalServerError $e) {

                    $this->db->rollBack();
                    (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
                }
            }

            /**
             * Edits an existing employee's information.
             *
             * @param int $employeeId The ID of the employee to edit.
             */
            public function editEmployee($employeeId)
            {
                try {

                    $this->db->beginTransaction();

                    $data = json_decode(file_get_contents("php://input"), true);

                    $this->validateEmployeeInputData($data);

                    $this->validateEmployeeId($employeeId);

                    $facilityEmployee  = new EmployeeService();

                    $facilityEmployee->edit($data, $employeeId);

                    $this->db->commit();


                    (new Status\Ok(['message' => 'Employee updated successfully!']))->send();
                } catch (Exceptions\BadRequest $e) {

                    $this->db->rollBack();
                    (new Status\BadRequest(['message' => $e->getMessage()]))->send();
                } catch (Exceptions\InternalServerError $e) {

                    $this->db->rollBack();
                    (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
                } catch (Exceptions\NotFound $e) {

                    $this->db->rollBack();
                    (new Status\NotFound(['message' => $e->getMessage()]))->send();
                }
            }

            /**
             * Deletes an employee by their ID.
             *
             * @param int $employeeId The ID of the employee to delete.
             */
            public function deleteEmployee($employeeId)
            {
                try {

                    $this->db->beginTransaction();

                    $this->validateEmployeeId($employeeId);

                    $facilityEmployee  = new EmployeeService();

                    $facilityEmployee->delete($employeeId);

                    $this->db->commit();

                    (new Status\Ok(['message' => 'Employee successfully deleted!']))->send();
                } catch (Exceptions\BadRequest $e) {

                    $this->db->rollBack();
                    (new Status\BadRequest(['message' => $e->getMessage()]))->send();
                } catch (Exceptions\InternalServerError $e) {

                    $this->db->rollBack();
                    (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
                } catch (Exceptions\NotFound $e) {

                    $this->db->rollBack();
                    (new Status\NotFound(['message' => $e->getMessage()]))->send();
                }
            }


            // OTHER FUNCTIONS:

            /**
             * Validates the input data for creating or editing an employee.
             *
             * @param array $data The input data to validate.
             * @throws Exceptions\BadRequest If the input data is invalid.
             */
            private function validateEmployeeInputData($data)
            {
                // first name
                if (!isset($data['first_name']) || !is_string($data['first_name']) || strlen($data['first_name']) > 255 || empty($data['first_name'])) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's first name must be a string, less than 256 characters and it can't be empty."]);
                }

                // last name
                if (!isset($data['last_name']) || !is_string($data['last_name']) || strlen($data['last_name']) > 255 || empty($data['first_name'])) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's last name must be a string, less than 256 characters and it can't be empty."]);
                }

                // role
                if (!isset($data['role']) || !is_string($data['role']) || strlen($data['role']) > 255 || empty($data['first_name'])) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's role must be a string, less than 256 characters and it can't be empty."]);
                }

                // facility_id
                if (isset($data['facility_id']) || empty($data['facility_id'])) {
                    if (!filter_var($data['facility_id'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 2147483647)))) {
                        throw new Exceptions\BadRequest(['message' => "Bad Request. facilty ID must be an integer between 1 and 2147483647 and it can't be empty."]);
                    }
                }

                // email
                if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Employee's email must be a valid format, less than 256 characters and it can't be empty."]);
                }
            }

            /**
             * Validates the provided employee ID.
             *
             * @param int $employeeId The ID of the employee to validate.
             * @throws Exceptions\BadRequest If the employee ID is not valid.
             */
            public function validateEmployeeId($employeeId)
            {
                if (isset($employeeId)) {
                    if (!filter_var($employeeId, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 2147483647)))) {
                        throw new Exceptions\BadRequest(['message' => 'Bad Request. employee ID must be an integer between 1 and 2147483647.']);
                    }
                }
            }
        }
    } else {

        throw new Exceptions\Unauthorized(['message' => 'Unauthorized. User not logged in']);
    }
} catch (Exceptions\Unauthorized $e) {

    (new Status\Unauthorized(['message' => $e->getMessage()]))->send();
}
