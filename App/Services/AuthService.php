<?php

namespace App\Services;

use App\Plugins\Di\Injectable;
use App\Plugins\Http\Exceptions;


class AuthService extends Injectable
{

    /**
     * Checks if a given username is already taken.
     * 
     * @param array $data An associative array containing a 'username' key.
     * 
     * @return void
     * 
     * @throws Exceptions\InternalServerError If there's an error fetching user from the database.
     */
    public function doesUsernameIsTaken($data)
    {
        $username = $data['username'];

        $query = "SELECT * FROM users WHERE username = '$username'";
        if (!$this->db->executeQuery($query)) {
            throw new Exceptions\InternalServerError(['message' => 'Internal Server Error. Error fetching user from the database.']);
        }
    }

    /**
     * Hashes a given password and inserts the username and hashed password into the database.
     * 
     * @param array $data An associative array containing 'username' and 'password' keys.
     * 
     * @return void
     */
    public function hashPassword($data)
    {
        $password = $data['password'];

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
        $this->db->executeQuery($query, [$data['username'], $passwordHash]);
    }
}
