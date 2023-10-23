<?php

namespace App\Models;

use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;

class Employee extends Injectable
{
    private $first_name;
    private $last_name;
    private $role;
    private $facility_id;
    private $email;

    public function __construct($first_name, $last_name, $role, $facility_id, $email)
    {
        $this->setFirstName($first_name);
        $this->setLastName($last_name);
        $this->setRole($role);
        $this->setFacilityId($facility_id);
        $this->setEmail($email);
    }

    // The validations in the model can be reactivated to provide an additional layer of data integrity.

    // first_name
    public function getFirstName()
    {
        return $this->first_name;
       
    }

    public function setFirstName($first_name)
    {
        // if (!is_string($first_name) || strlen($first_name) > 255) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's first name must be a string and less than 256 characters."]);
        // }

        // if (empty($first_name)) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Couldn't insert first name"]);
        // }

        $this->first_name = $first_name;
    }

    // last_name
    public function getLastName()
    {
        return $this->last_name;
    }

    public function setLastName($last_name)
    {
        // if (!is_string($last_name) || strlen($last_name) > 255) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's last name must be a string and less than 256 characters."]);
        // }

        // if (empty($last_name)) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Couldn't insert last name"]);
        // }

        $this->last_name = $last_name;
    }

    // role
    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        // if (!is_string($role) || strlen($role) > 255) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Each employee's role must be a string and less than 256 characters."]);
        // }

        // if (empty($role)) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Couldn't insert role"]);
        // }

        $this->role = $role;
    }

    // facility_id
    public function getFacilityId()
    {
        return $this->facility_id;
    }

    public function setFacilityId($facility_id)
    {
        // if (!filter_var($facility_id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 2147483647)))) {
        //         throw new Exceptions\BadRequest(['message' => 'Bad Request. employee ID must be an integer between 1 and 2147483647.']);
        // }

        // if (empty($facility_id)) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Couldn't insert facility id"]);
        // }

        $this->facility_id = $facility_id;
    }

    // email
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        // if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Must to be an email and less than 256 characters."]);
        // }

        // if (empty($email)) {
        //     throw new Exceptions\BadRequest(['message' => "Bad Request. Couldn't insert email"]);
        // }

        $this->email = $email;
    }
}
