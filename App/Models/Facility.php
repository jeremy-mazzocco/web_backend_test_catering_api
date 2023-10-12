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
    private $name;
    private $creation_date;
    private $location_id;
    private $tags = [];

    public function __construct($name, $creation_date, $location_id, $tags = [])
    {
        $this->setName($name);
        $this->setCreationDate($creation_date);
        $this->setLocationId($location_id);
        $this->setTags($tags);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new \Exception("Name must be a non-empty string");
        }
        $this->name = $name;
    }

    public function getCreationDate()
    {
        return $this->creation_date;
    }

    public function setCreationDate($creation_date)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $creation_date);

        if (!$date || $date->format('Y-m-d') !== $creation_date) {
            throw new \Exception("Empty date or invalid date format");
        }

        if (empty($creation_date)) {
            throw new \Exception("Creation date cannot be empty");
        }

        $this->creation_date = $creation_date;
    }

    public function getLocationId()
    {
        return $this->location_id;
    }

    public function setLocationId($location_id)
    {
        if (!is_int($location_id) || ($location_id < 1 || $location_id > 7)) {
            throw new \Exception("Location is not present in the database");
        }
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
