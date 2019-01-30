<?php

global $routes;
$routes = [];

$routes['/teste'] = '/home/testando';
$routes['/usuarios/{id}'] = '/home/visualizar_usuarios/:id';
