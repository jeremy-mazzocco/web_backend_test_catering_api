<?php

/** @var Bramus\Router\Router $router */

// Test routes
$router->get('/test', App\controllers\IndexController::class . '@test');

// Auth
$router->post('/register', App\Controllers\AuthController::class . '@registerUser');
$router->post('/login', App\Controllers\AuthController::class . '@loginUser');
$router->post('/logout', App\Controllers\AuthController::class . '@logoutUser');


// show all data
$router->get('/facilities', App\Controllers\FacilityController::class . '@getAllFacilities');

// search
$router->post('/search/facilities', App\Controllers\FacilityController::class . '@searchFacilities');

// create
$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');
$router->post('/employee', App\Controllers\EmployeeController::class . '@createEmployee');

// show by ID
$router->get('/facility/{id}', App\Controllers\FacilityController::class . '@getFacilityById');
$router->get('/employee/{id}', App\Controllers\EmployeeController::class . '@getEmployeeById');

// edit
$router->put('/facility/{id}', App\Controllers\FacilityController::class . '@editFacility');
$router->put('/employee/{id}', App\Controllers\EmployeeController::class . '@editEmployee');

// delete
$router->delete('/facility/{id}', App\Controllers\FacilityController::class . '@deleteFacility');
$router->delete('/employee/{id}', App\Controllers\EmployeeController::class . '@deleteEmployee');
