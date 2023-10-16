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
        if (!is_string($name) || strlen($name) > 255) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Facility name must be a string and less than 256 characters.']);
        }

        if (empty($name)) {
            throw new Exceptions\BadRequest(['message' => "Bad Request. Couldn't insert name"]);
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
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Creation date must be in the format YYYY-MM-DD.']);
        }

        if (empty($creation_date)) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Couldn/t insert creation_date']);
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
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Location ID must be a number between 1 and 7.']);
        }

        if (empty($location_id)) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Couldn/t insert lcation_id']);
        }

        $this->location_id = $location_id;
    }

    // tags
    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        // array tags
        if (!is_array($tags) || count($tags) > 5) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. You can only add up to 5 tags. Tags must to be in an array']);
        }

        // each tag
         foreach ($tags as $tag) {
            if (is_numeric($tag) || empty($tag)) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. Tags must be non-numeric strings and cannot be empty.']);
            }
        }
        
        $this->tags = $tags;
    }
}
