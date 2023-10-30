<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Services\AuthService;

class AuthController extends BaseController
{
    /**
     * Registers a new user.
     * 
     * @return void
     * 
     * @throws Exceptions\BadRequest
     * @throws Exceptions\InternalServerError
     */
    public function registerUser()
    {
        try {
            $this->db->beginTransaction();

            $data = json_decode(file_get_contents("php://input"), true);
            $this->validateUserData($data);

            $authService = new AuthService();

            $authService->doesUsernameIsTaken($data);

            if ($this->db->getResults()) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. User already registered']);
            }

            $authService->hashPassword($data);

            $userId = 1;

            $token = bin2hex(random_bytes(32));

            $expiryTime = (new \DateTime())->modify('+1 hour');

            $this->db->executeQuery("INSERT INTO access_tokens (token, user_id, expiry) VALUES (?, ?, ?)", [$token, $userId, $expiryTime->format('Y-m-d H:i:s')]);

            $this->db->commit();

            (new Status\Created(['message' => 'User successfully registered!']))->send();
        } catch (Exceptions\BadRequest $e) {
            $this->db->rollBack();
            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {
            $this->db->rollBack();
            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        }
    }

    /**
     * Log in a user.
     * 
     * @return void
     * 
     * @throws Exceptions\BadRequest
     * @throws Exceptions\InternalServerError
     */
    public function loginUser()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);

            $this->validateUserData($data);

            $authService  = new AuthService();

            $authService->doesUsernameIsTaken($data);

            if (!$user = $this->db->getResults()) {
                throw new Exceptions\NotFound(['Message' => 'Not Found. No user found with the provided ID.']);
            }
            $user = $user[0];

            $password = $data['password'];

            if (!password_verify($password, $user['password_hash'])) {
                throw new Exceptions\Unauthorized(['message' => 'Bad Request. User or password not correct']);
            }

            $token = bin2hex(random_bytes(32));

            $expiryTime = (new \DateTime())->modify('+1 hour');

            $this->db->executeQuery("INSERT INTO access_tokens (token, user_id, expiry) VALUES (?, ?, ?)", [$token, $user['id'], $expiryTime->format('Y-m-d H:i:s')]);

            $_SESSION['authenticated'] = true;

            (new Status\Ok(['message' => 'User successfully logged!', 'access_token' => $token]))->send();
        } catch (Exceptions\BadRequest $e) {

            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {

            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        }
    }

    /**
     * Logs out a user.
     * 
     * @return void
     * 
     * @throws Exceptions\BadRequest
     */
    public function logoutUser()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);
            $token = $data['access_token'];

            $this->db->executeQuery("DELETE FROM access_tokens WHERE token = ?", [$token]);


            if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

                session_unset();
                session_destroy();
            } else {

                throw new Exceptions\BadRequest(['message' => 'Bad Request. You are not logged in.']);
            }

            (new Status\Ok(['message' => 'User successfully logout!']))->send();
        } catch (Exceptions\BadRequest $e) {

            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        }
    }

    /**
     * Verifies if the given token is valid.
     * 
     * @param string $token The access token to be verified.
     * 
     * @return int Returns the associated user ID of the token.
     * 
     * @throws Status\Unauthorized
     */
    public function verifyToken($token)
    {
        $this->db->executeQuery("SELECT * FROM access_tokens WHERE token = ?", [$token]);
        $tokenData = $this->db->getResults()[0] ?? null;

        if (!$tokenData || new \DateTime() > new \DateTime($tokenData['expiry'])) {
            (new Status\Unauthorized(['message' => 'Token is invalid or expired']))->send();
            exit();
        }

        return $tokenData['user_id'];
    }


    // OTHER FUNCTIONS:

    /**
     * Validates user data for correctness.
     * 
     * @param array $data User data with keys 'username' and 'password'.
     * 
     * @return void
     * 
     * @throws Exceptions\BadRequest
     */
    private function validateUserData($data)
    {

        if (empty($data['username']) || empty($data['password'])) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Password or Username are required.']);
        }

        if (strlen($data['username']) < 4 || strlen($data['password']) < 4) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Password or Username must be al least 4 characters.']);
        }

        if (strlen($data['username']) > 255 || strlen($data['password']) > 255) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Password or Username must be minimun 255 characters.']);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Username can only contain letters, numbers, and underscores.']);
        }
    }
}
