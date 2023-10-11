<?php

/** @var Bramus\Router\Router $router */

// Define routes here

$router->get('/', App\Controllers\FacilityController::class . '@getAllFacilities');
$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');
$router->get('/facility/{id}', App\Controllers\FacilityController::class . '@getFacilityById');
$router->put('/facility/{id}', App\Controllers\FacilityController::class . '@editFacility');
$router->delete('/facility/{id}', App\Controllers\FacilityController::class . '@deleteFacility');



// $router->get('/', App\Controllers\IndexController::class . '@test');
