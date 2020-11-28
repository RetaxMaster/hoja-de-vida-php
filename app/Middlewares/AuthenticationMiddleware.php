<?php

namespace App\Middlewares;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface {

    /**
     * Process an incoming server request|
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {

        if($request->getUri()->getPath() === "/hoja-de-vida-php/admin") {

            $sessionUserId = $_SESSION["userId"] ?? null;

            if (!$sessionUserId) {
                return new EmptyResponse(401);
            }

        }

        return $handler->handle($request);

    }

}


?>