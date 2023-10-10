<?php

/** @var Bramus\Router\Router $router */

// Define routes here
// $router->get('/test', App\Controllers\IndexController::class . '@test');
// $router->get('/', App\Controllers\IndexController::class . '@index');


$router->get('/facilities', App\Controllers\FacilityController::class . '@getAllFacilities');
