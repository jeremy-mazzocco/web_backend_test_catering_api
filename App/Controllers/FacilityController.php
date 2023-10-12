<?php

namespace App\Controllers;

use App\Models\Facility;
use App\Models\Tag;
use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Plugins\Http\ApiException;
use App\Plugins\Db\Db;

class FacilityController extends BaseController
{

    // GET ALL FACILITIES
    public function getAllFacilities()
    {
        try {
            // Pagination parameters
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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
        } catch (ApiException $e) {
            $e->send();
        }
    }


    // GET FACILITY BY ID
    public function getFacilityById($id)
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Get the facility from the database
            $queryFacility = 'SELECT * FROM Facility WHERE id = :id';
            $bindFacility = ['id' => $id];
            if (!$this->db->executeQuery($queryFacility, $bindFacility)) {
                throw new Exceptions\InternalServerError('Error fetching the facility.');
            }

            $facility = $this->db->getResults();

            // Check if Facility is in the database
            if (empty($facility)) {
                (new Exceptions\NotFound(['message' => 'Facility not found']))->send();
                return;
            }

            $facility = $facility[0];

            // Get the location of the facility
            $queryLocation = 'SELECT * FROM Location WHERE id = :location_id';
            $bindLocation = ['location_id' => $facility['location_id']];
            if (!$this->db->executeQuery($queryLocation, $bindLocation)) {
                throw new Exceptions\InternalServerError('Error fetching the location of the facility.');
            }

            $location = $this->db->getResults();
            $facility['location'] = $location[0];

            // Get the tags associated with the facility
            $queryTag = 'SELECT * FROM Tag
            JOIN Facility_Tag ON Facility_Tag.tag_id = Tag.id
            WHERE Facility_Tag.facility_id = :facility_id';
            $bindTag = ['facility_id' => $id];
            if (!$this->db->executeQuery($queryTag, $bindTag)) {
                throw new Exceptions\InternalServerError('Error fetching the location of the facility.');
            }

            $tags = $this->db->getResults();
            $facility['tags'] = $tags;

            //Save transaction
            $this->db->commit();

            (new Status\Ok($facility))->send();
        } catch (\Exception $e) {

            // Rollback transaction
            $this->db->rollBack();

            (new Status\InternalServerError(['message' => 'Error fetching facility details.', 'error' => $e->getMessage()]))->send();
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

            // Validation of the sent data
            if (!isset($data['name']) || !isset($data['creation_date']) || !isset($data['location_id'])) {
                throw new \Exception('Required data missing.');
            }

            // Create a Facility model
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
            $this->db->executeQuery($query, $bind);
            $existingFacility = $this->db->getResults();

            if ($existingFacility) {
                throw new \Exception('This facility in this location already exist.');
            }

            // Check if the location_id exists in the Location table
            $query = 'SELECT id FROM Location WHERE id = :location_id';
            $bind = ['location_id' => $facility->getLocationId()];
            $this->db->executeQuery($query, $bind);
            $existingLocation = $this->db->getResults();

            if (!$existingLocation) {
                throw new \Exception('Location with the specified does not exist.');
            }

            // Check if the creation_date is a correct format
            if (isset($data['creation_date']) && strtotime($data['creation_date']) === false) {
                throw new \Exception('Invalid Date format.');
            }

            // Insert the facility into the database
            $query = 'INSERT INTO Facility (name, creation_date, location_id) VALUES (:name, :creation_date, :location_id)';
            $bind = [
                'name' => $facility->getName(),
                'creation_date' => $facility->getCreationDate(),
                'location_id' => $facility->getLocationId()
            ];
            if (!$this->db->executeQuery($query, $bind)) {
                throw new \Exception('Error while creating the facility.');
            }

            // Get the ID of the newly created facility
            $facilityId = $this->db->getLastInsertedId();

            // Check and handle tags
            if (isset($data['tags']) && is_array($data['tags'])) {
                foreach ($data['tags'] as $tag) {

                    if (!isset($tag)) {
                        throw new \Exception('Incomplete tag data.');
                    }

                    $tagName = $tag;

                    // Check if the tag already exists
                    $query = 'SELECT id FROM Tag WHERE name = :name';
                    $bind = ['name' => $tagName];
                    $this->db->executeQuery($query, $bind);
                    $existingTag = $this->db->getResults();

                    // If the tag exists, get its ID. Otherwise, return an error.
                    if ($existingTag) {
                        $tagId = $existingTag[0]['id'];
                    } else {
                        throw new \Exception('Tag does not exist.');
                    }

                    // Create the association between the facility and the tag
                    $query = 'INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
                    $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];
                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new \Exception('Error in associating facility and tag.');
                    }
                }
            }

            // Save transaction
            $this->db->commit();

            (new Status\Ok(['message' => 'Facility and tags created successfully!']))->send();
        } catch (\Exception $e) {

            // Rollback the transaction
            $this->db->rollBack();

            (new Status\InternalServerError(['message' => 'Error in creating facility and tags.', 'error' => $e->getMessage()]))->send();
        }
    }

    // EDIT A FACILITY
    public function editFacility($facilityId)
    {
        try {
            // Begin trasaction
            $this->db->beginTransaction();

            // Check if the facility with the specified ID exists
            $query = 'SELECT id FROM Facility WHERE id = :facility_id';
            $bind = ['facility_id' => $facilityId];
            $this->db->executeQuery($query, $bind);
            $existingFacility = $this->db->getResults();

            if (!$existingFacility) {
                throw new \Exception('Facility with the specified ID does not exist.');
            }

            // Get data from the request body and decode
            $data = json_decode(file_get_contents("php://input"), true);

            // Validation of the sent data
            if (!isset($data['name']) || !isset($data['creation_date']) || !isset($data['location_id'])) {
                throw new \Exception('Required data missing.');
            }

            // Check if the location_id exists in the Location table
            $query = 'SELECT id FROM Location WHERE id = :location_id';
            $bind = ['location_id' => $data['location_id']];
            $this->db->executeQuery($query, $bind);
            $existingLocation = $this->db->getResults();

            if (!$existingLocation) {
                throw new \Exception('Location with the specified does not exist.');
            }

            // Check if the creation_date is a correct format
            if (isset($data['creation_date']) && strtotime($data['creation_date']) === false) {
                throw new \Exception('Invalid Date format.');
            }

            // Update the facility in the database
            $query = 'UPDATE Facility SET name = :name, creation_date = :creation_date, location_id = :location_id WHERE id = :facility_id';
            $bind = [
                'name' => $data['name'],
                'creation_date' => $data['creation_date'],
                'location_id' => $data['location_id'],
                'facility_id' => $facilityId
            ];
            if (!$this->db->executeQuery($query, $bind)) {
                throw new \Exception('Error while updating the facility.');
            }

            // Remove existing tags associations for the facility
            $query = 'DELETE FROM Facility_Tag WHERE facility_id = :facility_id';
            $bind = ['facility_id' => $facilityId];
            $this->db->executeQuery($query, $bind);

            // Check and handle tags
            if (isset($data['tags']) && is_array($data['tags'])) {
                foreach ($data['tags'] as $tag) {

                    // Validation of tag data
                    if (!isset($tag)) {
                        throw new \Exception('Incomplete tag data.');
                    }

                    $tagName = $tag;

                    // Check if the tag already exists
                    $query = 'SELECT id FROM Tag WHERE name = :name';
                    $bind = ['name' => $tagName];
                    $this->db->executeQuery($query, $bind);
                    $existingTag = $this->db->getResults();

                    // If the tag exists, get its ID
                    if ($existingTag) {
                        $tagId = $existingTag[0]['id'];
                    } else {
                        // Tag doesn't exist, return an error
                        throw new \Exception('Tag does not exist.');
                    }

                    // Create the association between the facility and the tag
                    $query = 'INSERT INTO Facility_Tag (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
                    $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];
                    if (!$this->db->executeQuery($query, $bind)) {
                        throw new \Exception('Error in associating facility and tag.');
                    }
                }
            }

            // Save transaction
            $this->db->commit();;

            (new Status\Ok(['message' => 'Facility and tags updated successfully!']))->send();
        } catch (\Exception $e) {

            // Rollback the transaction in case of an error
            $this->db->rollBack();

            (new Status\InternalServerError(['message' => 'Error in updating facility and tags.', 'error' => $e->getMessage()]))->send();
        }
    }


}
