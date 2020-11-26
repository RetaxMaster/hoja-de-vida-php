<?php

// Inicializamos los errores
ini_set("display_errors", 1);
ini_set("display_startup_error", 1);
error_reporting(E_ALL);

require("../vendor/autoload.php");

use Illuminate\Database\Capsule\Manager as Capsule;
use Aura\Router\RouterContainer;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'cursophp',
    'username'  => 'retaxmaster',
    'password'  => '123',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

// Se encargará manejar los request, cumple con psr-7
$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

// Istancio la ruta de aura router
$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();

// El router nos regresa lo que nosotros le dijimos que regresara cuando haga match con cierta ruta, en este caso regresa el arreglo con controller y action... si, puede regresar tambien una cadena del tipo IndexController@indexAction como Laravel...
$map->get('index', '/hoja-de-vida-php/', [
    "controller" => "App\Controllers\IndexController",
    "action" => "indexAction"
]);

$map->get('addJobs', '/hoja-de-vida-php/jobs/add', [
    "controller" => "App\Controllers\JobsController",
    "action" => "getAddJobAction"
]);

$map->post('saveJobs', '/hoja-de-vida-php/jobs/add', [
    "controller" => "App\Controllers\JobsController",
    "action" => "getAddJobAction"
]);

$map->get('addUser', '/hoja-de-vida-php/users/add', [
    'controller' => 'App\Controllers\UsersController',
    'action' => 'getAddUser'
]);

$map->post('saveUser', '/hoja-de-vida-php/users/save', [
    'controller' => 'App\Controllers\UsersController',
    'action' => 'postSaveUser'
]);

$map->get('loginForm', '/hoja-de-vida-php/login', [
    'controller' => 'App\Controllers\AuthController',
    'action' => 'getLogin'
]);

$map->post('auth', '/hoja-de-vida-php/auth', [
    'controller' => 'App\Controllers\AuthController',
    'action' => 'postLogin'
]);

$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);

if(!$route) {
    echo "No route";
}
else {

    $handlerData = $route->handler;
    $constrollerName = $handlerData["controller"];
    $actionName = $handlerData["action"];

    $controller = new $constrollerName;
    $response = $controller->$actionName($request);

    echo $response->getBody();

    // El request es el que maneja Zend:D!
    // El response viene desde BaseController en Twig, que igual es manejado por Zend

}

?>