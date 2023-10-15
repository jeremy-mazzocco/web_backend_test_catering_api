<?php

/** @var Bramus\Router\Router $router */

// Test routes
// $router->get('/', App\Controllers\IndexController::class . '@test');

$router->get('/test', App\Controllers\IndexController::class . '@test');

// Get all facilities
$router->get('/facilities', App\Controllers\FacilityController::class . '@getAllFacilities');

// Search
$router->post('/search/facilities', App\Controllers\FacilityController::class . '@searchFacilities');

// Create
$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');

// Get one facility
$router->get('/facility/{id}', App\Controllers\FacilityController::class . '@getFacilityById');

// Edit
$router->put('/facility/{id}', App\Controllers\FacilityController::class . '@editFacility');

// Delete
$router->delete('/facility/{id}', App\Controllers\FacilityController::class . '@deleteFacility');




