<?php

namespace App\Controllers;

use App\Models\Message;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;

class ContactController extends BaseController {
    
    public function index() {

        return $this->renderHTML("contacts/index.twig");

    }

    public function send(ServerRequest $request) {

        $requestData = $request->getParsedBody();

        $message = new Message();
        $message->name = $requestData['name'];
        $message->email = $requestData['email'];
        $message->message = $requestData['message'];
        $message->sent = false;
        $message->save();

        return new RedirectResponse("/hoja-de-vida-php/contact");

    }

}


?>