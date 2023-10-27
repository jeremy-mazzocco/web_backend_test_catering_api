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
         * Costruttore della classe Facility.
         *
         * @param string $name Il nome della struttura.
         * @param string $creation_date La data di creazione nel formato 'YYYY-MM-DD'.
         * @param int $location_id L'ID della posizione della struttura.
         * @param array $tags Un array di tag associati alla struttura.
         */

        $this->setName($name);
        $this->setCreationDate($creation_date);
        $this->setLocationId($location_id);
        $this->setTags($tags);
    }


    // The validations in the model can be reactivated to provide an additional layer of data integrity.

    
    /**
     * Restituisce il nome della struttura.
     *
     * @return string Il nome della struttura.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Imposta il nome della struttura.
     *
     * @param string $name Il nome della struttura.
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
     * Restituisce la data di creazione della struttura nel formato 'YYYY-MM-DD'.
     *
     * @return string La data di creazione della struttura.
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * Imposta la data di creazione della struttura.
     *
     * @param string $creation_date La data di creazione nel formato 'YYYY-MM-DD'.
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
     * Restituisce l'ID della posizione della struttura.
     *
     * @return int L'ID della posizione.
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Imposta l'ID della posizione della struttura.
     *
     * @param int $location_id L'ID della posizione.
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
     * Restituisce un array di tag associati alla struttura.
     *
     * @return array Un array di tag.
     */
    public function getTags()
    {
        return $this->tags;
    }

     /**
     * Imposta un array di tag associati alla struttura.
     *
     * @param array $tags Un array di tag.
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
