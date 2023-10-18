<?php

namespace App\Services;

use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


class AuthService extends Injectable
{
    public function isUsernameTaken($data)
    {
        $username = $data['username'];

        // check if user is already registered
        $query = "SELECT * FROM users WHERE username = '$username'";
        if (!$this->db->executeQuery($query)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Error fetching facilities from the database.']);
        } 
    }

    public function hashPassword($data)
    {
        // Hash password
        $password = $data['password'];
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
        $this->db->executeQuery($query, [$data['username'], $passwordHash]);
    }
}
