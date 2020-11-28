<?php

// Inicializamos los errores
ini_set("display_errors", 1);
ini_set("display_startup_error", 1);
error_reporting(E_ALL);

require("../vendor/autoload.php");

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;
use Aura\Router\RouterContainer;
use Laminas\Diactoros\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use WoohooLabs\Harmony\Harmony;
use WoohooLabs\Harmony\Middleware\DispatcherMiddleware;
use WoohooLabs\Harmony\Middleware\LaminasEmitterMiddleware;

$container = new DI\Container();
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV["DB_HOST"],
    'database'  => $_ENV["DB_NAME"],
    'username'  => $_ENV["DB_USER"],
    'password'  => $_ENV["DB_PASS"],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

// Se encargará manejar los request, cumple con psr-7
$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
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
    "App\Controllers\IndexController",
    "indexAction"
]);

$map->get('indexJobs', '/hoja-de-vida-php/jobs', [
    "App\Controllers\JobsController",
    "indexAction"
]);

$map->get('addJobs', '/hoja-de-vida-php/jobs/add', [
    "App\Controllers\JobsController",
    "getAddJobAction"
]);

$map->get('deleteJobs', '/hoja-de-vida-php/jobs/delete', [
    "App\Controllers\JobsController",
    "deleteAction"
]);

$map->post('saveJobs', '/hoja-de-vida-php/jobs/add', [
    "App\Controllers\JobsController",
    "getAddJobAction"
]);

$map->get('addUser', '/hoja-de-vida-php/users/add', [
    'App\Controllers\UsersController',
    'getAddUser'
]);

$map->post('saveUser', '/hoja-de-vida-php/users/save', [
    'App\Controllers\UsersController',
    'postSaveUser'
]);

$map->get('loginForm', '/hoja-de-vida-php/login', [
    'App\Controllers\AuthController',
    'getLogin'
]);

$map->post('auth', '/hoja-de-vida-php/auth', [
    'App\Controllers\AuthController',
    'postLogin'
]);

$map->get('admin', '/hoja-de-vida-php/admin', [
    'App\Controllers\AdminController',
    'getIndex',
    "auth" => true
]);

$map->get('logout', '/hoja-de-vida-php/logout', [
    'App\Controllers\AuthController',
    'getLogout',
    "auth" => true
]);

$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);

if(!$route) {
    echo "No route";
}
else {

    $handlerData = $route->handler;
    /* $controllerName = $handlerData["controller"];
    $actionName = $handlerData["action"]; */ 
    
    // $controllerName tiene la ruta(namespace) completo de nuestra clase (App\Controllers\JobController por ejemplo), entonces PHP-DI mediante el método get de su container se encargará de ver qué cosa es lo que el constructor necesita y se lo inyectará
    /* $controller = $container->get($controllerName);
    $response = $controller->$actionName($request);

    foreach ($response->getHeaders() as $name => $values) {

        foreach ($values as $value) {
            header(sprintf("%s: %s", $name, $value), false);
        }

    }

    http_response_code($response->getStatusCode());
    echo $response->getBody(); */

    // El request es el que maneja Laminas:D!
    // El response viene desde BaseController en Twig, que igual es manejado por Laminas

    // Aquí empieza la parte de Harmony:D!

    $harmony = new Harmony($request, new Response());
    $harmony
        ->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()))
        ->addMiddleware(new \App\Middlewares\AuthenticationMiddleware())
        ->addMiddleware(new Middlewares\AuraRouter($routerContainer))
        // Podemos pasarle un contenedor de inyección de dependencias compatible y el nombre del action, en este caso, Laminas lo llama request-handler
        ->addMiddleware(new DispatcherMiddleware($container, "request-handler"))
        ->run();


}

?>