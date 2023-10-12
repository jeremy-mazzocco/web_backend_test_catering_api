<?php

namespace App\Models;

use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


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

    // name
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if (!is_string($name)) {
            throw new Exceptions\BadRequest;
        }

        if (empty($name)) {
            throw new Exceptions\NotFound;
        }

        $this->name = $name;
    }

    // date
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    public function setCreationDate($creation_date)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $creation_date);

        if (!$date || $date->format('Y-m-d') !== $creation_date) {
            throw new Exceptions\BadRequest;
        }

        if (empty($creation_date)) {
            throw new Exceptions\NotFound;
        }

        $this->creation_date = $creation_date;
    }

    // location
    public function getLocationId()
    {
        return $this->location_id;
    }

    public function setLocationId($location_id)
    {
        if (!is_numeric($location_id) || ($location_id < 1 || $location_id > 7)) {
            throw new Exceptions\BadRequest;
        }

        if (empty($location_id)) {
            throw new Exceptions\NotFound;
        }

        $this->location_id = $location_id;
    }

    // tag
    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {

        if (!is_array($tags['tags']) || count($tags['tags']) > 5) {
            throw new Exceptions\BadRequest;
        }

        $this->tags = $tags;
    }
}
