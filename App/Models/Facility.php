<?php

namespace App\Models;

use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


class Facility extends Injectable
{
    /** @var string */
    private $name;
    /** @var string */
    private $creation_date;
    /** @var int */
    private $location_id;
    /** @var array */
    private $tags = [];


    public function __construct($name, $creation_date, $location_id, $tags = [])
    {
        /**
         * Constructor for the Facility class.
         *
         * @param string $name The name of the facility.
         * @param string $creation_date The creation date in the 'YYYY-MM-DD' format.
         * @param int $location_id The ID of the facility's location.
         * @param array $tags An array of tags associated with the facility.
         */
        $this->setName($name);
        $this->setCreationDate($creation_date);
        $this->setLocationId($location_id);
        $this->setTags($tags);
    }


    // The validations in the model can be reactivated to provide an additional layer of data integrity.


    /**
     * Get the name of the facility.
     *
     * @return string The name of the facility.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the facility.
     *
     * @param string $name The name of the facility.
     */
    public function setName($name)
    {
        // if (!is_string($name) || strlen($name) > 255) {
        //     throw new Exceptions\BadRequest(['message' => 'Bad Request. Facility name must be a string and less than 256 characters.']);
        // }

        // if (empty($name)) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Couldn't insert name"]);
        // }

        $this->name = $name;
    }


    /**
     * Returns the creation date of the facility in 'YYYY-MM-DD' format.
     *
     * @return string The creation date of the facility.
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * Sets the creation date of the facility.
     *
     * @param string $creation_date The creation date in 'YYYY-MM-DD' format.
     */
    public function setCreationDate($creation_date)
    {
        // $date = \DateTime::createFromFormat('Y-m-d', $creation_date);

        // if (!$date || $date->format('Y-m-d') !== $creation_date) {
        //     throw new Exceptions\BadRequest(['message' => 'Bad Request. Creation date must be in the format YYYY-MM-DD.']);
        // }

        // if (empty($creation_date)) {
        //     throw new Exceptions\BadRequest(['message' => 'Bad Request. Couldn/t insert creation_date']);
        // }

        $this->creation_date = $creation_date;
    }


    /**
     * Returns the ID of the facility's location.
     *
     * @return int The ID of the location.
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Sets the ID of the facility's location.
     *
     * @param int $location_id The ID of the location.
     */
    public function setLocationId($location_id)
    {
        // if (!is_numeric($location_id) || ($location_id < 1 || $location_id > 7)) {
        //     throw new Exceptions\BadRequest(['message' => 'Bad Request. Location ID must be a number between 1 and 7.']);
        // }

        // if (empty($location_id)) {
        //     throw new Exceptions\BadRequest(['message' => 'Bad Request. Couldn/t insert lcation_id']);
        // }

        $this->location_id = $location_id;
    }


    /**
     * Returns an array of tags associated with the facility.
     *
     * @return array An array of tags.
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Sets an array of tags associated with the facility.
     *
     * @param array $tags An array of tags.
     */
    public function setTags($tags)
    {
        // // array tags
        // if (!is_array($tags) || count($tags) > 5) {
        //     throw new Exceptions\BadRequest(['message' => 'Bad Request. You can only add up to 5 tags. Tags must to be in an array']);
        // }

        // // each tag
        // foreach ($tags as $tag) {
        //     if (is_numeric($tag) || empty($tag)) {
        //         throw new Exceptions\BadRequest(['message' => 'Bad Request. Tags must be non-numeric strings and cannot be empty.']);
        //     }
        // }

        $this->tags = $tags;
    }
}
