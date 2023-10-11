<?php

/** @var Bramus\Router\Router $router */

// Define routes here

$router->get('/', App\Controllers\FacilityController::class . '@getAllFacilities');
$router->post('/facility', App\Controllers\FacilityController::class . '@createFacility');




// $router->get('/', App\Controllers\IndexController::class . '@test');
