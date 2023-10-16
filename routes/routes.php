<?php

/** @var Bramus\Router\Router $router */

// Test routes
// $router->get('/', App\Controllers\IndexController::class . '@test');
$router->get('/test', App\Controllers\IndexController::class . '@test');


$router->get('/facilities', App\Controllers\FacilityController::class . '@getAllFacilities');

$router->post('/search/facilities', App\Controllers\FacilityController::class . '@searchFacilities');

$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');

// $router->post('/employee', App\Controllers\FacilityController::class . '@createEmployee');

$router->get('/facility/{id}', App\Controllers\FacilityController::class . '@getFacilityById');

$router->put('/facility/{id}', App\Controllers\FacilityController::class . '@editFacility');

$router->delete('/facility/{id}', App\Controllers\FacilityController::class . '@deleteFacility');

$router->delete('/employee/{id}', App\Controllers\FacilityController::class . '@deleteEmployee');
