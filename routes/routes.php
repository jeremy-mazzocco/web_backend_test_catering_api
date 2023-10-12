<?php

/** @var Bramus\Router\Router $router */

// Define routes here


// Show all facilities
$router->get('/', App\Controllers\FacilityController::class . '@getAllFacilities');

// Create a facility
$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');

// Show one facility
$router->get('/facility/{id}', App\Controllers\FacilityController::class . '@getFacilityById');

// Edit a facility
$router->put('/facility/{id}', App\Controllers\FacilityController::class . '@editFacility');







// $router->get('/', App\Controllers\IndexController::class . '@test');
