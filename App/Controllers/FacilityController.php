<?php

namespace App\Controllers;

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
                throw new Exceptions\BadRequest;
            }
            if ($page < 1) {
                throw new Exceptions\BadRequest;
            }

            $offset = ($page - 1) * $limit;

            // Fetch all the facilities with pagination
            $query = 'SELECT * FROM Facility LIMIT ' . $limit . ' OFFSET ' . $offset;

            if ($this->db->executeQuery($query)) {
                $facilities = $this->db->getResults();
            } else {
                throw new Exceptions\InternalServerError();
            }

            // For each facility, attach associated location and tags
            foreach ($facilities as &$facility) {

                // Fetch the location associated with the current facility
                $query = 'SELECT * 
                     FROM Location 
                     WHERE id = :location_id';
                $bind = ['location_id' => $facility['location_id']];

                if ($this->db->executeQuery($query, $bind)) {
                    $location = $this->db->getResults();
                    $facility['location'] = $location[0] ?? null;
                } else {
                    throw new Exceptions\InternalServerError();
                }

                // Fetch tags associated with the current facility
                $facilityId = $facility['id'];
                $query = 'SELECT name 
                      FROM Tag 
                      JOIN Facility_Tag ON Tag.id = Facility_Tag.tag_id 
                      WHERE Facility_Tag.facility_id = :facility_id;';
                $bind = ['facility_id' => $facilityId];

                if ($this->db->executeQuery($query, $bind)) {
                    $tags = $this->db->getResults();
                    $facility['tags'] = array_column($tags, 'name');
                } else {
                    throw new Exceptions\InternalServerError();
                }
            }

            // Return the result
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
            $this->db->executeQuery($query, $bind);
            $facility = $this->db->getResults();

            // Check if the facility is found
            if (empty($facility)) {
                throw new Exceptions\NotFound;
            }
            $facility = $facility[0];

            // Get the location of the facility
            $query = 'SELECT * FROM Location WHERE id = :location_id';
            $bind = ['location_id' => $facility['location_id']];
            $this->db->executeQuery($query, $bind);
            $location = $this->db->getResults();

            // Check if the location is found
            if (empty($location)) {
                throw new Exceptions\NotFound;
            }

            $facility['location'] = $location[0];

            // Get the tags associated with the facility
            $query = 'SELECT * FROM Tag
            JOIN Facility_Tag ON Facility_Tag.tag_id = Tag.id
            WHERE Facility_Tag.facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];

            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError;
            }

            $tags = $this->db->getResults();
            $facility['tags'] = $tags;


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
            // Begin trasaction
            $this->db->beginTransaction();

            // Get data from the request body and decode
            $data = json_decode(file_get_contents("php://input"), true);
            var_dump($data);

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
                throw new Exceptions\InternalServerError;
            }
            $existingFacility = $this->db->getResults();
            if ($existingFacility) {
                throw new Exceptions\BadRequest;
            }

            // Insert the facility into the database
            $query = 'INSERT INTO Facility (name, creation_date, location_id) VALUES (:name, :creation_date, :location_id)';
            $bind = [
                'name' => $facility->getName(),
                'creation_date' => $facility->getCreationDate(),
                'location_id' => $facility->getLocationId()
            ];
            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError;
            }

            // Get the ID of the newly created facility
            $facilityId = $this->db->getLastInsertedId();

            // Check and handle tags
            foreach ($data['tags'] as $tag) {

                $tagName = $tag;

                // Check if the tag already exists
                $query = 'SELECT id FROM Tag WHERE name = :name';
                $bind = ['name' => $tagName];
                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError;
                }
                $existingTag = $this->db->getResults();

                // If the tag exists, get its ID. Otherwise, create a new tag.
                if ($existingTag) {
                    $tagId = $existingTag[0]['id'];
                } else {
                    // Tag doesn't exist, so create it
                    $query = 'INSERT INTO Tag (name) VALUES (:name)';
                    $bind = ['name' => $tagName];

                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError;
                    }

                    // Get the ID of the newly created tag
                    $tagId = $this->db->getLastInsertedId();
                }

                // Create the association between the facility and the tag
                $query = 'INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
                $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];

                if (!$this->db->executeQuery($query, $bind)) {
                    throw new Exceptions\InternalServerError;
                }
            }

            // Save transaction
            $this->db->commit();

            (new Status\Created(['message' => 'Facility and tags created successfully!']))->send();
        } catch (Exceptions\BadRequest $e) {

            // Rollback the transaction
            $this->db->rollBack();
            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
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
                throw new Exceptions\InternalServerError;
            }
            $existingFacility = $this->db->getResults();
            if (!$existingFacility) {
                throw new Exceptions\BadRequest;
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
                throw new Exceptions\InternalServerError;
            }

            // Remove existing tag associations for this facility
            $query = 'DELETE FROM Facility_Tag WHERE facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];
            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError;
            }

            // Handle tags if provided
            if (!empty($facility->getTags())) {
                foreach ($facility->getTags() as $tagName) {

                    // Get tag ID from database
                    $query = 'SELECT id FROM Tag WHERE name = :name';
                    $bind = ['name' => $tagName];
                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError;
                    }
                    $existingTag = $this->db->getResults();

                    // If the tag exists, get its ID
                    if ($existingTag) {
                        $tagId = $existingTag[0]['id'];
                    } else {
                        // Tag does not exist create it
                        $query = 'INSERT INTO Tag (name) VALUES (:name)';
                        $bind = ['name' => $tagName];
                        if (!$this->db->executeQuery($query, $bind)) {
                            throw new Exceptions\InternalServerError;
                        }

                        // Get the ID of the new created tag
                        $tagId = $this->db->getLastInsertedId();
                    }

                    // Create the association between the facility and the tag
                    $query = 'INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
                    $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];
                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new Exceptions\InternalServerError;
                    }
                }
            }

            // Commit the transaction
            $this->db->commit();

            // Send success response
            (new Status\Ok(['message' => 'Facility and tags updated successfully!']))->send();
        } catch (Exceptions\BadRequest $e) {

            // Rollback 
            $this->db->rollBack();
            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {
            // Rollback the transaction in case of an error
            $this->db->rollBack();
            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\NotFound $e) {
            // Rollback the transaction in case of an error
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
                throw new Exceptions\InternalServerError;
            }
            $existingFacility = $this->db->getResults();

            if (!$existingFacility) {
                throw new Exceptions\NotFound;
            }

            // Delete the relationships between the facility and tags
            $query = 'DELETE FROM Facility_Tag WHERE facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];
            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError;
            }

            // Delete the facility
            $query = 'DELETE FROM Facility WHERE id = :id';
            $bind = ['id' => $facilityId];
            if (!$this->db->executeQuery($query, $bind)) {
                throw new Exceptions\InternalServerError;
            }

            // Commit the transaction
            $this->db->commit();

            (new Status\Ok(['message' => 'Facility and its associated tags successfully deleted!']))->send();
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
            // // Pagination, get data from client
            // $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
            // $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

            // // Validate limit and page
            // if ($limit < 1 || $limit > 100) {
            //     throw new Exceptions\BadRequest;
            // }
            // if ($page < 1) {
            //     throw new Exceptions\BadRequest;
            // }

            // $offset = ($page - 1) * $limit;


            // Prendere i parametri dalla richiesta
            $data = json_decode(file_get_contents("php://input"), true);
            $bind = [];
            $conditions = [];

            $query = "SELECT DISTINCT f.* FROM Facility f 
                  LEFT JOIN Facility_Tag ft ON f.id = ft.facility_id
                  LEFT JOIN Tag t ON ft.tag_id = t.id
                  LEFT JOIN Location l ON f.location_id = l.id";

            // Se viene fornito il nome della struttura, aggiungilo alle condizioni
            if (!empty($data['facility_name'])) {
                $conditions[] = "f.name LIKE :facility_name";
                $bind['facility_name'] = '%' . $data['facility_name'] . '%';
            }

            // Se viene fornito il nome del tag, aggiungilo alle condizioni
            if (!empty($data['tag_name'])) {
                $conditions[] = "t.name LIKE :tag_name";
                $bind['tag_name'] = '%' . $data['tag_name'] . '%';
            }

            // Se viene fornita la città della località, aggiungila alle condizioni
            if (!empty($data['city'])) {
                $conditions[] = "l.city LIKE :city";
                $bind['city'] = '%' . $data['city'] . '%';
            }

            // Se ci sono delle condizioni, aggiungile alla query
            if ($conditions) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }

            // Eseguire la query
            if (!$this->db->executeQuery($query, $bind)) {
                throw new \Exception('Errore nella ricerca delle strutture.');
            }

            $results = $this->db->getResults();

            // Rispondere con i risultati
            (new Status\Ok($results))->send();
        } catch (\Exception $e) {
            (new Status\InternalServerError(['message' => 'Errore nella ricerca.', 'error' => $e->getMessage()]))->send();
        }
    }


    // VALIDATION FUNCTIONS
    private function validateFacilityData($data)
    {
        // Check the required fields
        if (!isset($data['name']) || !isset($data['creation_date']) || !isset($data['location_id'])) {
            throw new Exceptions\BadRequest;
        }

        // Validate the date format
        if (strtotime($data['creation_date']) === false) {
            throw new Exceptions\BadRequest;
        }

        // Validate location_id
        if (!is_numeric($data['location_id']) || $data['location_id'] < 1 || $data['location_id'] > 7) {
            throw new Exceptions\BadRequest;
        }

        // Validate tags
        if (!is_array($data['tags']) || count($data['tags']) > 5) {
            throw new Exceptions\BadRequest;
        }
    }

    private function validateFacilityId($facilityId)
    {
        if (!is_numeric($facilityId) || $facilityId <= 0) {
            throw new Exceptions\BadRequest;
        }
    }
}
