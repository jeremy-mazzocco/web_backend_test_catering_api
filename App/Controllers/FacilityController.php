<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Facility;
use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;


class FacilityController extends BaseController
{

    // GET ALL FACILITIES
    public function getAllFacilities()
    {
        try {
            // Pagination, get data from client
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

            // Validate limit and page
            if ($limit < 1 || $limit > 100) {
                throw new Exceptions\BadRequest('Limit must be between 1 and 100.');
            }
            if ($page < 1) {
                throw new Exceptions\BadRequest('Page number must be positive.');
            }

            $offset = ($page - 1) * $limit;

            // Fetch all the facilities with pagination
            $query = 'SELECT * FROM Facility LIMIT ' . $limit . ' OFFSET ' . $offset;

            if (!$this->db->executeQuery($query)) {
                throw new Exceptions\InternalServerError('Error fetching facilities from the database.');
            }

            $facilities = $this->db->getResults();

            // For each facility, attach associated location and tags
            foreach ($facilities as &$facility) {

                // Fetch the location associated with the current facility
                $query = 'SELECT * FROM Location WHERE id = :location_id';
                $bind = ['location_id' => $facility['location_id']];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError('Error fetching location data for the facility.');
                }

                $location = $this->db->getResults();
                $facility['location'] = $location[0] ?? null;

                // Fetch tags associated with the current facility
                $facilityId = $facility['id'];
                $query = 'SELECT name FROM Tag JOIN Facility_Tag ON Tag.id = Facility_Tag.tag_id WHERE Facility_Tag.facility_id = :facility_id;';
                $bind = ['facility_id' => $facilityId];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError('Error fetching tag data for the facility.');
                }

                $tags = $this->db->getResults();
                $facility['tags'] = array_column($tags, 'name');

                // Fetch employees associated with the current facility
                $query = 'SELECT * FROM Employee WHERE facility_id = :id';
                $bind = ['id' => $facility['id']];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError('Error fetching employees for the facility.');
                }

                $employees = $this->db->getResults();
                $facility['employees'] = $employees;
            }

            (new Status\Ok($facilities))->send();
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
            // VALIDATIONS of the id
            $this->validateFacilityId($facilityId);

            // Get the facility from the database
            $query = 'SELECT * FROM Facility WHERE id = :id';
            $bind = ['id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Failed to retrieve the facility.');
            }
            if (!$facility = $this->db->getResults()) {
                throw new Exceptions\NotFound('No facility found with the provided ID.');
            }
            $facility = $facility[0];

            // Get the location of the facility
            $query = 'SELECT * FROM Location WHERE id = :location_id';
            $bind = ['location_id' => $facility['location_id']];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Failed to retrieve the location for the facility.');
            }
            if (!$location = $this->db->getResults()) {
                throw new Exceptions\NotFound('Location associated with the facility not found.');
            }
            $facility['location'] = $location[0];

            // Get the tags associated with the facility
            $query = 'SELECT * FROM Tag
        JOIN Facility_Tag ON Facility_Tag.tag_id = Tag.id
        WHERE Facility_Tag.facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Failed to retrieve tags for the facility.');
            }
            if (!$tags = $this->db->getResults()) {
                throw new Exceptions\NotFound('No tags found for the specified facility.');
            }
            $facility['tags'] = $tags;

            // Fetch employees associated with the current facility
            $query = 'SELECT * 
           FROM Employee  
           WHERE facility_id = :id';
            $bind = ['id' => $facility['id']];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Failed to retrieve employees for the facility.');
            }
            if (!$employees = $this->db->getResults()) {
                throw new Exceptions\NotFound('No employees found for the specified facility.');
            }
            $facility['employees'] = $employees;


            (new Status\Ok($facility))->send();
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
            // Begin transaction
            $this->db->beginTransaction();

            // Get data from the request body and decode
            $data = json_decode(file_get_contents("php://input"), true);

            // VALIDATIONS of the sent data
            $this->validateFacilityData($data);

            // CREATE using model
            $facility = new Facility(
                $data['name'],
                $data['creation_date'],
                $data['location_id']
            );

            // Check if a facility with the same name and location_id already exists
            $query = 'SELECT id FROM Facility WHERE name = :name AND location_id = :location_id';
            $bind = [
                'name' => $facility->getName(),
                'location_id' => $facility->getLocationId()
            ];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError(['message' => 'Failed to execute query during facility verification.']);
            }
            if ($existingFacility = $this->db->getResults()) {
                throw new Exceptions\BadRequest(['message' => 'A facility with this name already exists at the specified location.']);
            }

            // Insert the facility into the database
            $query = 'INSERT INTO Facility (name, creation_date, location_id) VALUES (:name, :creation_date, :location_id)';
            $bind = [
                'name' => $facility->getName(),
                'creation_date' => $facility->getCreationDate(),
                'location_id' => $facility->getLocationId()
            ];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError(['message' => 'Failed to create new facility.']);
            }

            // Get the ID of the newly created facility
            $facilityId = $this->db->getLastInsertedId();

            // Check and handle tags
            if (isset($data['tags'])) {
                foreach ($data['tags'] as $tag) {

                    $tagName = $tag;

                    // Check if the tag already exists
                    $query = 'SELECT id FROM Tag WHERE name = :name';
                    $bind = ['name' => $tagName];

                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError(['message' => 'Failed during tag existence check.']);
                    }

                    // If the tag exists, get its ID
                    if ($existingTag = $this->db->getResults()) {
                        $tagId = $existingTag[0]['id'];
                    } else {
                        // If the tag doesn't exist, create it
                        $query = 'INSERT INTO Tag (name) VALUES (:name)';
                        $bind = ['name' => $tagName];

                        if (!$this->db->executeQuery($query, $bind)) {
                            throw new Exceptions\InternalServerError(['message' => 'Failed to create new tag.']);
                        }
                        if (!$tagId = $this->db->getLastInsertedId()) {
                            throw new Exceptions\InternalServerError(['message' => 'Failed to retrieve new tag ID.']);
                        }
                    }

                    // Create the association between the facility and the tag
                    $query = 'INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
                    $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];

                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError(['message' => 'Failed to associate tag with facility.']);
                    }
                }
            }

            // Create the new employees
            if (isset($data['employees'])) {
                foreach ($data['employees'] as $employeeData) {

                    if (!isset($employeeData['email'])) {
                        throw new Exceptions\BadRequest(['message' => 'Employee data incomplete, email is missing.']);
                    }

                    // Check if the email is already in use
                    $emailQuery = 'SELECT id FROM Employee WHERE email = :email';
                    $emailBind = ['email' => $employeeData['email']];

                    if (!$this->db->executeQuery($emailQuery, $emailBind)) {
                        throw new Exceptions\InternalServerError(['message' => 'Failed to execute query during employee email verification.']);
                    }

                    if ($this->db->getResults()) {
                        throw new Exceptions\BadRequest(['message' => 'An employee with this email already exists.']);
                    }

                    $employee = new Employee(
                        $employeeData['first_name'],
                        $employeeData['last_name'],
                        $employeeData['role'],
                        $facilityId,
                        $employeeData['email']
                    );

                    // Insert the employee into the database
                    $query = 'INSERT INTO Employee (first_name, last_name, role, facility_id, email) VALUES (:first_name, :last_name, :role, :facility_id, :email)';
                    $bind = [
                        'first_name' => $employee->getFirstName(),
                        'last_name' => $employee->getLastName(),
                        'role' => $employee->getRole(),
                        'facility_id' => $employee->getFacilityId(),
                        'email' => $employee->getEmail()
                    ];

                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError(['message' => 'Failed to create new employee.']);
                    }
                }
            }

            // Save transaction
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
            // Begin transaction
            $this->db->beginTransaction();

            // Get data from the request body and decode it
            $data = json_decode(file_get_contents("php://input"), true);

            // VALIDATE the sent data
            $this->validateFacilityData($data);
            $this->validateFacilityId($facilityId);

            // Create a Facility model instance and set its properties
            $facility = new Facility($data['name'], $data['creation_date'], $data['location_id'], $data['tags']);

            // Check if the facility exists
            $query = 'SELECT id FROM Facility WHERE id = :facility_id';
            $bind = ['facility_id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError(['message' => 'Failed to execute query during facility verification.']);
            }
            if (!$existingFacility = $this->db->getResults()) {
                throw new Exceptions\BadRequest(['message' => 'Facility does not exist.']);
            }

            // Update the facility in the database
            $query = 'UPDATE Facility SET name = :name, creation_date = :creation_date, location_id = :location_id WHERE id = :facility_id';
            $bind = [
                'name' => $facility->getName(),
                'creation_date' => $facility->getCreationDate(),
                'location_id' => $facility->getLocationId(),
                'facility_id' => $facilityId
            ];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError(['message' => 'Failed to update facility.']);
            }

            // Remove existing tag associations for this facility
            $query = 'DELETE FROM Facility_Tag WHERE facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError(['message' => 'Failed to remove existing tag associations.']);
            }

            // Handle tags if provided
            if (!empty($facility->getTags())) {
                foreach ($facility->getTags() as $tagName) {

                    // Get tag ID from database
                    $query = 'SELECT id FROM Tag WHERE name = :name';
                    $bind = ['name' => $tagName];

                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError(['message' => 'Failed during tag existence check.']);
                    }
                    if (!$existingTag = $this->db->getResults()) {
                        // Tag does not exist, create it
                        $query = 'INSERT INTO Tag (name) VALUES (:name)';
                        $bind = ['name' => $tagName];
                        if (!$this->db->executeQuery($query, $bind)) {
                            throw new Exceptions\InternalServerError(['message' => 'Failed to create new tag.']);
                        }

                        // Get the ID of the newly created tag
                        $tagId = $this->db->getLastInsertedId();
                    } else {
                        $tagId = $existingTag[0]['id'];
                    }

                    // Create the association between the facility and the tag
                    $query = 'INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
                    $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];

                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError(['message' => 'Failed to associate tag with facility.']);
                    }
                }
            }

            // Handle employee edits
            foreach ($data['employees'] as $employeeData) {

                // Check if email is set and not empty
                if (!isset($employeeData['email']) || empty($employeeData['email'])) {
                    throw new Exceptions\BadRequest(['message' => 'Employee data incomplete, email is missing.']);
                }

                // Create an Employee model instance
                $employee = new Employee(
                    $employeeData['first_name'],
                    $employeeData['last_name'],
                    $employeeData['role'],
                    $facilityId,
                    $employeeData['email']
                );

                // Check if the employee exists for the given email
                $query = 'SELECT id FROM Employee WHERE email = :email';
                $bind = ['email' => $employeeData['email']];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError(['message' => 'Failed to execute query during employee verification.']);
                }
                if (!$existingEmployee = $this->db->getResults()) {
                    throw new Exceptions\NotFound(['message' => 'Employee not found.']);
                }

                // Update the existing employee in the database
                $query = 'UPDATE Employee SET first_name = :first_name, last_name = :last_name, role = :role, facility_id = :facility_id WHERE email = :email';
                $bind = [
                    'first_name' => $employee->getFirstName(),
                    'last_name' => $employee->getLastName(),
                    'role' => $employee->getRole(),
                    'facility_id' => $employee->getFacilityId(),
                    'email' => $employee->getEmail()
                ];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError(['message' => 'Failed to update employee.']);
                }
            }

            // Commit transaction
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
            // Begin transaction
            $this->db->beginTransaction();

            // VALIDATE the sent data
            $this->validateFacilityId($facilityId);

            // Check if the facility with the specified ID exists
            $query = 'SELECT id FROM Facility WHERE id = :id';
            $bind = ['id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Error querying the facility.');
            }
            if (!$existingFacility = $this->db->getResults()) {
                throw new Exceptions\NotFound('The facility with the specified ID does not exist.');
            }

            // Delete the employees associated with the facility
            $query = 'DELETE FROM Employee WHERE facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Error deleting associated employees.');
            }

            // Delete the relationships between the facility and tags
            $query = 'DELETE FROM Facility_Tag WHERE facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Error deleting tag associations.');
            }

            // Delete the facility
            $query = 'DELETE FROM Facility WHERE id = :id';
            $bind = ['id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError('Error deleting the facility.');
            }

            // Commit the transaction
            $this->db->commit();

            (new Status\Ok(['message' => 'Facility and its associated tags and employees successfully deleted!']))->send();
        } catch (Exceptions\BadRequest $e) {

            // Rollback
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
            // Pagination
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

            // Validate limit and page
            if ($limit < 1 || $limit > 100) {
                throw new Exceptions\BadRequest("Invalid limit value. Limit should be between 1 and 100.");
            }
            if ($page < 1) {
                throw new Exceptions\BadRequest("Invalid page number. Page number should be greater than 0.");
            }

            $offset = ($page - 1) * $limit;

            // Get parameters from the request body
            $data = json_decode(file_get_contents("php://input"), true);
            $bind = [];
            $conditions = [];

            // VALIDATIONS of the sent data
            $this->validateFacilityDataSearch($data);

            // Query to join all tables
            $query = "SELECT DISTINCT Facility.* FROM Facility 
                LEFT JOIN Facility_Tag ON Facility.id = Facility_Tag.facility_id
                LEFT JOIN Tag ON Facility_Tag.tag_id = Tag.id
                LEFT JOIN Location ON Facility.location_id = Location.id";

            // If a facility name is provided, add it to the conditions
            if (!empty($data['name'])) {
                $conditions[] = "Facility.name LIKE :name";
                $bind['name'] = '%' . $data['name'] . '%';
            }

            // If a tag name is provided, add it to the conditions
            if (!empty($data['tags'])) {
                $conditions[] = "Tag.name LIKE :tags";
                $bind['tags'] = '%' . $data['tags'] . '%';
            }

            // If a city is provided, add it to the conditions
            if (!empty($data['location'])) {
                $conditions[] = "Location.city LIKE :location";
                $bind['location'] = '%' . $data['location'] . '%';
            }

            // If there are any conditions, attach them to the query
            if ($conditions) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }

            // Add pagination to the query
            $query .= " LIMIT $limit OFFSET $offset";

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError("Failed to execute the search query.");
            }
            if (!$results = $this->db->getResults()) {
                throw new Exceptions\BadRequest("No facilities found with the provided search criteria.");
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
                    throw new Exceptions\InternalServerError("Failed to retrieve tags for the facility.");
                }
            }

            (new Status\Ok($results))->send();
        } catch (Exceptions\BadRequest $e) {

            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {

            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        }
    }


    // VALIDATION FUNCTIONS:

    // Validation data from user
    private function validateFacilityData($data)
    {
        // Check the required fields
        if (!isset($data['name']) || !isset($data['creation_date']) || !isset($data['location_id'])) {
            throw new Exceptions\BadRequest(['message' => 'Name, creation date, and location ID are required.']);
        }

        // Validate the name length and string
        if (isset($data['name'])) {
            if (!is_string($data['name']) || strlen($data['name']) > 255) {
                throw new Exceptions\BadRequest(['message' => 'Facility name must be a string and less than 256 characters.']);
            }
        }

        // Validate the date format
        $creationDate = $data['creation_date'];
        $date = \DateTime::createFromFormat('Y-m-d', $creationDate);

        if (!$date || $date->format('Y-m-d') !== $creationDate) {
            throw new Exceptions\BadRequest(['message' => 'Creation date must be in the format YYYY-MM-DD.']);
        }

        // Validate location_id
        if (!is_numeric($data['location_id']) || $data['location_id'] < 1 || $data['location_id'] > 7) {
            throw new Exceptions\BadRequest(['message' => 'Location ID must be a number between 1 and 7.']);
        }

        // Validate tags
        if (isset($data['tags'])) {
            if ((!is_array($data['tags']) || count($data['tags']) > 5)) {
                throw new Exceptions\BadRequest(['message' => 'You can only add up to 5 tags. Tags must to be in an array']);
            }
            // Check each tag is not numeric or empty string
            foreach ($data['tags'] as $tag) {
                if (is_numeric($tag) || empty($tag)) {
                    throw new Exceptions\BadRequest(['message' => 'Tags must be non-numeric strings and cannot be empty.']);
                }
            }
        }

        // Validate employees data
        if (isset($data['employees'])) {
            if (!is_array($data['employees'])) {
                throw new Exceptions\BadRequest(['message' => 'Employees data must be in an array.']);
            }

            foreach ($data['employees'] as $employee) {

                // Validate employee's first name
                if (!isset($employee['first_name']) || !is_string($employee['first_name']) || strlen($employee['first_name']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Each employee's first name must be a string and less than 256 characters."]);
                }

                // Validate employee's last name
                if (!isset($employee['last_name']) || !is_string($employee['last_name']) || strlen($employee['last_name']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Each employee's last name must be a string and less than 256 characters."]);
                }

                // Validate employee's role
                if (!isset($employee['role']) || !is_string($employee['role']) || strlen($employee['role']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Each employee's role must be a string and less than 256 characters."]);
                }

                // Validate email
                if (!isset($employee['email']) || !filter_var($employee['email'], FILTER_VALIDATE_EMAIL) || strlen($employee['email']) > 255) {
                    throw new Exceptions\BadRequest(['message' => "Each employee's email must be a valid format and less than 256 characters."]);
                }
            }
        }
    }

    // Validation ID
    private function validateFacilityId($facilityId)
    {
        if (isset($facilityId)) {
            if (!filter_var($facilityId, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 2147483647)))) {
                throw new Exceptions\BadRequest(['message' => 'Facility ID must be an integer between 1 and 2147483647.']);
            }
        }
    }

    // Validation Facility Search
    private function validateFacilityDataSearch($data)
    {
        foreach ($data as $dataInput) {

            if (!is_string($dataInput) || strlen($dataInput) > 255) {
                throw new Exceptions\BadRequest(['message' => 'Search data must be a string and less than 256 characters.']);
            }
        }
    }
}
