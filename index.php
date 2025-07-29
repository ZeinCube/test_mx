<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Router\Router;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$router = new Router($request);
$response = $router->handle();

$response->send();
