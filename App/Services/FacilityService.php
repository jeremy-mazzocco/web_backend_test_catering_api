<?php

namespace App\Services;

use App\Models\Facility;
use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


class FacilityService extends Injectable
{
    /**
     * Retrieves all facilities with pagination.
     *
     * @param array $pagination Associative array containing 'limit' and 'offset' keys.
     * @return array Array of facilities with associated location, tags, and employees.
     * @throws Exceptions\InternalServerError When there is an error executing the query.
     * @throws Exceptions\BadRequest When there is an error fetching facilities.
     */
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

            $this->getLocationDataForFacility($facility);

            $this->getTagsForFacility($facility);

            $this->getEmployeesForFacility($facility);
        }

        return $facilities;
    }

    /**
     * Retrieves a facility by its ID.
     *
     * @param int $facilityId.
     * @return array Facility details with associated location, tags, and employees.
     * @throws Exceptions\InternalServerError When there is an error executing the query.
     * @throws Exceptions\NotFound When no facility is found for the given ID.
     */
    public function getByID($facilityId)
    {
        $query = 'SELECT * FROM Facility WHERE id = :id';
        $bind = ['id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Failed to retrieve the facility.']);
        }

        if (!$facility = $this->db->getResults()) {
            throw new Exceptions\NotFound(['Message' => 'Not Found. No facility found with the provided ID.']);
        }

        $facility = $facility[0];

        // Attach associated location, tags and employee
        $this->getLocationDataForFacility($facility);

        $this->getTagsForFacility($facility);

        $this->getEmployeesForFacility($facility);

        return $facility;
    }

    /**
     * Creates a new facility.
     *
     * @param array $data Associative array containing the facility data (name, creation_date, location_id, tags).
     * @return array Returns the created facility.
     * @throws Exceptions\InternalServerError When there is an error executing the query.
     * @throws Exceptions\BadRequest When a facility with the same name and location already exists.
     */
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
        if ($this->db->getResults()) {
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
            
            $this->associateTagWithFacility($tag, $facilityId);
        }

        return $facility;
    }

    /**
     * Edits an existing facility.
     *
     * @param int $facilityId.
     * @param array $data Associative array containing data to update the facility with.
     * @return array Returns the updated facility.
     * @throws Exceptions\InternalServerError Throws an exception if there's an error during the update.
     */
    public function edit($facilityId, $data)
    {

        $facility = new Facility(
            $data['name'],
            $data['creation_date'],
            $data['location_id'],
            $data['tags']
        );

        $this->doesFacilityExist($facilityId);

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

        $this->removeTagsFromFacility($facilityId);

        // Insert new tags
        foreach ($data['tags'] as $tag) {

            $this->associateTagWithFacility($tag, $facilityId);
        }

        return $facility;
    }

    /**
     * Deletes a facility and its associated.
     *
     * @param int $facilityId.
     * @throws Exceptions\InternalServerError Throws an exception if there's an error during deletion.
     */
    public function delete($facilityId)
    {

        $this->doesFacilityExist($facilityId);

        // Delete the employees associated with the facility
        $query = 'DELETE FROM Employee WHERE facility_id = :facility_id';
        $bind = ['facility_id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Error deleting associated employees.']);
        }

        $this->removeTagsFromFacility($facilityId);

        // Delete the facility
        $query = 'DELETE FROM Facility WHERE id = :id';
        $bind = ['id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Error deleting the facility.']);
        }
    }

    /**
     * Searches for facilities based on provided criteria.
     *
     * @param array $pagination array containing limit and offset.
     * @param array $data array containing search criteria.
     * @return array Returns an array of facilities that match the search criteria.
     * @throws Exceptions\InternalServerError Throws an exception if there's an error executing the search query.
     * @throws Exceptions\BadRequest Throws an exception if no facilities are found.
     */
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

            $this->getTagsForFacility($facility);
        }

        return $results;
    }


    // OTHER FUNCTIONS:


    /**
     * Retrieves location data for a given facility.
     *
     * @param array $facility Associative array containing facility data. The location data will be added to this array.
     * @return array Returns the location data.
     * @throws Exceptions\InternalServerError If there is an error executing the query.
     * @throws Exceptions\NotFound If no location is found for the given facility.
     */
    public function getLocationDataForFacility(&$facility)
    {
        $locationId = $facility['location_id'];

        $query = 'SELECT * FROM Location WHERE id = :location_id';
        $bind = ['location_id' => $locationId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['Message' => 'Internal Server Error. Failed to retrieve the location for the facility.']);
        }
        if (!$location = $this->db->getResults()) {
            throw new Exceptions\NotFound(['Message' => 'Not Found. Location associated with the facility not found.']);
        }

        $location = $location[0];
        $facility['location'] = $location;

        return $location;
    }

    /**
     * Retrieves tags associated with a given facility.
     *
     * @param array $facility Associative array containing facility data. The tags will be added to this array.
     * @return void
     * @throws Exceptions\InternalServerError If there is an error executing the query.
     */
    public function getTagsForFacility(&$facility)
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

        return;
    }

    /**
     * Retrieves employees associated with a given facility.
     *
     * @param array $facility Associative array containing facility data. The employees will be added to this array.
     * @return void
     * @throws Exceptions\InternalServerError If there is an error executing the query.
     */
    public function getEmployeesForFacility(&$facility)
    {
        $facilityId = $facility['id'];

        $query = 'SELECT * FROM Employee WHERE facility_id = :id';
        $bind = ['id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Error fetching employees from the database.']);
        }
        $employees = $this->db->getResults();

        $facility['employees'] = $employees;

        return;
    }

    /**
     * Associates a tag with a facility. If the tag doesn't exist, it will be created.
     *
     * @param string $tag The name of the tag to associate with the facility.
     * @param int $facilityId The ID of the facility to associate with the tag.
     * @return void
     * @throws Exceptions\InternalServerError If there is an error during the tag check, creating a new tag, or associating the tag with the facility.
     */
    public function associateTagWithFacility(&$tag, $facilityId)
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

    /**
     * Checks if a facility exists based on its ID.
     *
     * @param int $facilityId The ID of the facility to check.
     * @return void
     * @throws Exceptions\InternalServerError If there is an error executing the query.
     * @throws Exceptions\NotFound If no facility is found with the provided ID.
     */
    public function doesFacilityExist($facilityId)
    {
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
     * Removes all tag associations from a facility.
     *
     * @param int $facilityId The ID of the facility from which to remove the tag associations.
     * @return void
     * @throws Exceptions\InternalServerError If there is an error removing the tag associations.
     */
    public function removeTagsFromFacility($facilityId)
    {
        $query = 'DELETE FROM Facility_Tag WHERE facility_id = :facility_id';
        $bind = ['facility_id' => $facilityId];

        if (!$this->db->executeQuery($query, $bind)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Failed to remove existing tag associations.']);
        }
    }
}
