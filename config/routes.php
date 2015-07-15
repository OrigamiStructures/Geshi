<?php
use Cake\Routing\Router;

Router::plugin('Geshi', function ($routes) {

	$routes->connect('/samples/*', ['plugin' => 'Geshi', 'controller' => 'Samples']);

    $routes->fallbacks('InflectedRoute');
});
