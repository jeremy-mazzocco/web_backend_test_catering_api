<?php

/** @var Bramus\Router\Router $router */




// Show all facilities
$router->get('/', App\Controllers\FacilityController::class . '@getAllFacilities');

// Search Facility with parameters
$router->post('/search/facilities', App\Controllers\FacilityController::class . '@searchFacilities');

// Create a facility
$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');

// Show one facility
$router->get('/facility/{id}', App\Controllers\FacilityController::class . '@getFacilityById');

// Edit a facility
$router->put('/facility/{id}', App\Controllers\FacilityController::class . '@editFacility');

// Delete a facilty
$router->delete('/facility/{id}', App\Controllers\FacilityController::class . '@deleteFacility');



// $router->get('/', App\Controllers\IndexController::class . '@test');
