<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Services\EmployeeService;


// try {

//     if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
class EmployeeController extends BaseController
{

    // GET EMPLOYEE BY ID
    public function getEmployeeById($employeeId)
    {
        try {

            $this->validateId($employeeId);

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

    // CREATE AN EMPLOYEE
    public function createEmployee()
    {
        try {

            $this->db->beginTransaction();

            $data = json_decode(file_get_contents("php://input"), true);

            $this->validateEmployeeData($data);

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

    // EDIT A FACILITY
    public function editEmployee($employeeId)
    {
        try {

            $this->db->beginTransaction();

            $data = json_decode(file_get_contents("php://input"), true);

            $this->validateEmployeeData($data);

            $this->validateId($employeeId);

            $facilityEmployee  = new EmployeeService();

            $facilityEmployee->edit($data);

            $this->db->commit();


            (new Status\Ok(['message' => 'Empployee updated successfully!']))->send();
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

    // DELETE AN EMPLOYEE
    public function deleteEmployee($employeeId)
    {
        try {

            $this->db->beginTransaction();

            $this->validateId($employeeId);

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


    // // OTHER FUNCTIONS:
    private function validateEmployeeData($data)
    {
        // Employees data
        if (isset($data['employees'])) {
            if (!is_array($data['employees'])) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. Employees data must be in an array.']);
            }

            foreach ($data['employees'] as $employee) {

                // first name
                if (!isset($employee['first_name']) || !is_string($employee['first_name']) || strlen($employee['first_name']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's first name must be a string and less than 256 characters or mising field."]);
                }

                // last name
                if (!isset($employee['last_name']) || !is_string($employee['last_name']) || strlen($employee['last_name']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's last name must be a string and less than 256 characters or mising field."]);
                }

                // role
                if (!isset($employee['role']) || !is_string($employee['role']) || strlen($employee['role']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's role must be a string and less than 256 characters or mising field."]);
                }

                // facility_id
                if (isset($employee['facility_id']) || empty($employee['facility_id'])) {
                    if (!filter_var($employee['facility_id'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 2147483647)))) {
                        throw new Exceptions\BadRequest(['message' => 'Bad Request. facilty ID must be an integer between 1 and 2147483647.']);
                    }
                }
            }

            // email
            if (!isset($employee['email']) || !filter_var($employee['email'], FILTER_VALIDATE_EMAIL) || strlen($employee['email']) > 255) {
                throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's email must be a valid format and less than 256 characters or mising field."]);
            }
        }
    }

    private function validateId($employeeId)
    {
        if (isset($employeeId)) {
            if (!filter_var($employeeId, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 2147483647)))) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. employee ID must be an integer between 1 and 2147483647.']);
            }
        }
    }

    //     } else {
    //         throw new Exceptions\Unauthorized(['message' => 'Unauthorized. User not logged in']);
    //     }
    // } catch (Exceptions\Unauthorized $e) {

    //     (new Status\Unauthorized(['message' => $e->getMessage()]))->send();
    // }
}
