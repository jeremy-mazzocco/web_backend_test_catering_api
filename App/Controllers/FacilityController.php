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
            // Fetch all the facilities
            $query = 'SELECT * FROM Facility';

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

   
}
