<?php

/** @var Bramus\Router\Router $router */

// Test routes
$router->get('/test', App\Controllers\IndexController::class . '@test');


// Auth
// $router->post('/register', App\Controllers\AuthController::class . '@registerUser');
// $router->post('/login', App\Controllers\AuthController::class . '@loginUser');
// $router->post('/logout', App\Controllers\AuthController::class . '@logoutUser');

// Facilities
$router->get('/facilities', App\Controllers\FacilityController::class . '@getAllFacilities');

$router->post('/search/facilities', App\Controllers\FacilityController::class . '@searchFacilities');

$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');

$router->get('/facility/{id}', App\Controllers\FacilityController::class . '@getFacilityById');

$router->put('/facility/{id}', App\Controllers\FacilityController::class . '@editFacility');

$router->delete('/facility/{id}', App\Controllers\FacilityController::class . '@deleteFacility');

$router->delete('/employee/{id}', App\Controllers\FacilityController::class . '@deleteEmployee');
