<?php

namespace App\Models;

use App\Plugins\Di\Injectable;
use PDO;
use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Plugins\Http\ApiException;
use App\Plugins\Db\Db;
use App\Models\Tag;



class Facility extends Injectable
{
    public $name;
    public $creation_date;
    public $location_id;
    public $tags = [];

    public function __construct($name, $creation_date, $location_id, $tags = [])
    {
        $this->name = $name;
        $this->creation_date = $creation_date;
        $this->location_id = $location_id;
        $this->tags = $tags;
    }

        public function getName()
        {
            return $this->name;
        }
    
        public function setName($name)
        {
            $this->name = $name;
        }

        public function getCreationDate()
        {
            return $this->creation_date;
        }
    
        public function setCreationDate($creation_date)
        {
            $this->creation_date = $creation_date;
        }
    
        public function getLocationId()
        {
            return $this->location_id;
        }
    
        public function setLocationId($location_id)
        {
            $this->location_id = $location_id;
        }
    
        public function getTags()
        {
            return $this->tags;
        }
    
        public function setTags($tags)
        {
            $this->tags = $tags;
        }

   
}
