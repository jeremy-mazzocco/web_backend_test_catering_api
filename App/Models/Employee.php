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

    // first_name
    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setFirstName($first_name)
    {
        if (!is_string($first_name) || strlen($first_name) > 255) {
            throw new Exceptions\BadRequest;
        }

        if (empty($first_name)) {
            throw new Exceptions\NotFound;
        }

        $this->first_name = $first_name;
    }

    // last_name
    public function getLastName()
    {
        return $this->last_name;
    }

    public function setLastName($last_name)
    {
        if (!is_string($last_name) || strlen($last_name) > 255) {
            throw new Exceptions\BadRequest;
        }

        if (empty($last_name)) {
            throw new Exceptions\NotFound;
        }

        $this->last_name = $last_name;
    }

    // role
    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        if (!is_string($role) || strlen($role) > 255) {
            throw new Exceptions\BadRequest;
        }

        if (empty($role)) {
            throw new Exceptions\NotFound;
        }

        $this->role = $role;
    }

    // facility_id
    public function getFacilityId()
    {
        return $this->facility_id;
    }

    public function setFacilityId($facility_id)
    {
        if (!is_numeric($facility_id) || $facility_id <= 0) {
            throw new Exceptions\BadRequest;
        }

        if (empty($facility_id)) {
            throw new Exceptions\NotFound;
        }

        $this->facility_id = $facility_id;
    }

    // email
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            throw new Exceptions\BadRequest;
        }

        $this->email = $email;
    }
}
