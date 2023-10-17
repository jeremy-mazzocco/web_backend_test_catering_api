<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Services\FacilityService;


// try {

// if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
class FacilityController extends BaseController
{

    // GET ALL FACILITIES
    public function getAllFacilities()
    {
        try {

            // Pagination
            $pagination = $this->getPaginationParams();

            $facilityService  = new FacilityService();

            $results = $facilityService->AllFacilities($pagination);

            (new Status\Ok($results))->send();
        } catch (Exceptions\BadRequest $e) {

            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {

            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        }
    }

    // GET FACILITY BY ID
    public function getFacilityById($facilityId)
    {
        try {

            $this->validateId($facilityId);

            $facilityService  = new FacilityService();

            $results = $facilityService->getByID($facilityId);

            (new Status\Ok($results))->send();
        } catch (Exceptions\InternalServerError $e) {

            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\NotFound $e) {

            (new Status\NotFound(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\BadRequest $e) {

            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        }
    }

    // CREATE A FACILITY
    public function createFacility()
    {
        try {

            $this->db->beginTransaction();

            $data = json_decode(file_get_contents("php://input"), true);

            $this->validateFacilityData($data);

            $facilityService  = new FacilityService();

            $facilityService->create($data);

            $this->db->commit();


            (new Status\Created(['message' => 'Facility created successfully!']))->send();
        } catch (Exceptions\BadRequest $e) {

            $this->db->rollBack();
            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {

            $this->db->rollBack();
            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        }
    }


    // EDIT A FACILITY
    public function editFacility($facilityId)
    {
        try {

            $this->db->beginTransaction();

            $data = json_decode(file_get_contents("php://input"), true);

            $this->validateFacilityData($data);

            $this->validateId($facilityId);

            $facilityService  = new FacilityService();

            $facilityService->edit($facilityId, $data);

            $this->db->commit();


            (new Status\Ok(['message' => 'Facility updated successfully!']))->send();
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

    // DELETE A FACILITY
    public function deleteFacility($facilityId)
    {
        try {

            $this->db->beginTransaction();

            $this->validateId($facilityId);

            $facilityService  = new FacilityService();

            $facilityService->delete($facilityId);

            $this->db->commit();

            (new Status\Ok(['message' => 'Facility and its associated tags and employees successfully deleted!']))->send();
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

            $facilityService  = new FacilityService();

            $facilityService->deleteEmpl($employeeId);

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

    // SEARCH FACILITY
    public function searchFacilities()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);

            $pagination = $this->getPaginationParams();

            $this->validateFacilityDataSearch($data);

            $facilityService  = new FacilityService();

            $results = $facilityService->search($pagination, $data);

            (new Status\Ok($results))->send();
        } catch (Exceptions\BadRequest $e) {

            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {

            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        }
    }



    // OTHER FUNCTIONS:
    private function getPaginationParams()
    {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        if ($limit < 1 || $limit > 100) {
            throw new Exceptions\BadRequest(['Message' => 'Bad Request. Limit must be between 1 and 100.']);
        }
        if ($page < 1) {
            throw new Exceptions\BadRequest(['Message' => 'Bad Request. Page number must be positive.']);
        }

        $offset = ($page - 1) * $limit;
        return ['limit' => $limit, 'offset' => $offset];
    }

    private function validateFacilityData($data)
    {
        if (!isset($data['name']) || !isset($data['creation_date']) || !isset($data['location_id'])) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Name, creation date, and location ID are required.']);
        }

        // facility name
        if (isset($data['name'])) {
            if (!is_string($data['name']) || strlen($data['name']) > 255) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. Facility name must be a string and less than 256 characters.']);
            }
        }

        // date
        $creationDate = $data['creation_date'];
        $date = \DateTime::createFromFormat('Y-m-d', $creationDate);

        if (!$date || $date->format('Y-m-d') !== $creationDate) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Creation date must be in the format YYYY-MM-DD.']);
        }

        // location_id
        if (!is_numeric($data['location_id']) || $data['location_id'] < 1 || $data['location_id'] > 7) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Location ID must be a number between 1 and 7.']);
        }

        // array tags
        if (isset($data['tags'])) {
            if ((!is_array($data['tags']) || count($data['tags']) > 5)) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. You can only add up to 5 tags. Tags must to be in an array']);
            }
            // each tag
            foreach ($data['tags'] as $tag) {
                if (is_numeric($tag) || empty($tag)) {
                    throw new Exceptions\BadRequest(['message' => 'Bad Request. Tags must be non-numeric strings and cannot be empty.']);
                }
            }
        }

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

                // email
                if (!isset($employee['email']) || !filter_var($employee['email'], FILTER_VALIDATE_EMAIL) || strlen($employee['email']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's email must be a valid format and less than 256 characters or mising field."]);
                }
            }
        }
    }

    private function validateId($facilityId)
    {
        if (isset($facilityId)) {
            if (!filter_var($facilityId, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 2147483647)))) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. Facility ID must be an integer between 1 and 2147483647.']);
            }
        }
    }

    private function validateFacilityDataSearch($data)
    {
        foreach ($data as $dataInput) {

            if (!is_string($dataInput) || strlen($dataInput) > 255) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. Search data must be a string and less than 256 characters.']);
            }
        }
    }
}

