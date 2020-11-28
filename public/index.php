<?php

require("../vendor/autoload.php");

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

if ($_ENV["DEBUG"] === "true") {
    // Inicializamos los errores
    ini_set("display_errors", 1);
    ini_set("display_startup_error", 1);
    error_reporting(E_ALL);
}

use Illuminate\Database\Capsule\Manager as Capsule;
use Aura\Router\RouterContainer;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use WoohooLabs\Harmony\Harmony;
use WoohooLabs\Harmony\Middleware\DispatcherMiddleware;
use WoohooLabs\Harmony\Middleware\LaminasEmitterMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::WARNING));

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
    'getIndex'
]);

$map->get('logout', '/hoja-de-vida-php/logout', [
    'App\Controllers\AuthController',
    'getLogout'
]);

$map->get('contactForm', '/hoja-de-vida-php/contact', [
    'App\Controllers\ContactController',
    'index'
]);

$map->post('contactSend', '/hoja-de-vida-php/contact/send', [
    'App\Controllers\ContactController',
    'send'
]);

$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);

if(!$route) {
    echo "No route";
}
else {

    try {

        // Aquí empieza la parte de Harmony:D!
        $harmony = new Harmony($request, new Response());
        $harmony
            ->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()));

        if($_ENV["DEBUG"] === "true")
            $harmony->addMiddleware(new Franzl\Middleware\Whoops\WhoopsMiddleware());

        $harmony->addMiddleware(new \App\Middlewares\AuthenticationMiddleware())
            ->addMiddleware(new Middlewares\AuraRouter($routerContainer))
            // Podemos pasarle un contenedor de inyección de dependencias compatible y el nombre del action, en este caso, Laminas lo llama request-handler
            ->addMiddleware(new DispatcherMiddleware($container, "request-handler"))
            ->run();

    } catch (\Exception $th) {

        $log->error("This job was not found");
        $emitter = new SapiEmitter();
        $emitter->emit(new EmptyResponse(400));

    } catch(Error $e) { // Antes de PHP 7 no se podían cachar los errores

        $emitter = new SapiEmitter();
        $emitter->emit(new EmptyResponse(500));

    }

    // Exception y Error funcionan porque están usando la interfaz Throwable, interfaz que nosotros NO deberíamos implementar, en su lugar deberíamos heredar de Exception


}

?>