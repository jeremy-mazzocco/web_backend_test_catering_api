<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Services\AuthService;

class AuthController extends BaseController
{
    /**
     * Controller function used to test whether the project was set up properly.
     * @return void
     */

    // Authentication
    public function registerUser()
    {

        try {

            $this->db->beginTransaction();

            $data = json_decode(file_get_contents("php://input"), true);

            $this->validateUserData($data);

            $authService  = new AuthService();

            $authService->isUsernameTaken($data);

            if ($this->db->getResults()) {
                throw new Exceptions\BadRequest(['message' => 'Bad Request. User already registerd']);
            }

            $authService->hashPassword($data);


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



    public function loginUser()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);

            $this->validateUserData($data);

            $authService  = new AuthService();

            $authService->isUsernameTaken($data);

            if (!$user = $this->db->getResults()) {
                throw new Exceptions\NotFound(['Message' => 'Not Found. No facility found with the provided ID.']);
            }
            $user = $user[0];

            // Verify la password
            $password = $data['password'];
            if (!password_verify($password, $user['password_hash'])) {
                throw new Exceptions\Unauthorized(['message' => 'Bad Request. User or password not correct']);
            }

            $_SESSION['authenticated'] = true;


            (new Status\Ok(['message' => 'User successfully logged!']))->send();
        } catch (Exceptions\BadRequest $e) {


            (new Status\BadRequest(['message' => $e->getMessage()]))->send();
        } catch (Exceptions\InternalServerError $e) {


            (new Status\InternalServerError(['message' => $e->getMessage()]))->send();
        }
    }

    public function logoutUser()
    {
        try {

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


    // OTHER FUNCTIONS:

    private function validateUserData($data)
    {

        if (!isset($data['username']) || !isset($data['password'])) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Password or Username are required.']);
        }

        if (strlen($data['username'] < 1  || $data['password'] < 4 || strlen($data['username'] > 255 || $data['password'])) > 255) {
            throw new Exceptions\BadRequest(['message' => 'Bad Request. Password or Username minimun 4 character and maximun 255.']);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'] || $data['password'])) {
            throw new Exceptions\BadRequest(['message' => 'Password or Username can only contain letters, numbers, and underscores.']);
        }
    }
}
