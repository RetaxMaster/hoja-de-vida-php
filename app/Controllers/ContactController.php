<?php

namespace App\Controllers;

use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class ContactController extends BaseController {
    
    public function index() {

        return $this->renderHTML("contacts/index.twig");

    }

    public function send(ServerRequest $request) {

        $requestData = $request->getParsedBody();

        // Create the Transport
        $transport = (new Swift_SmtpTransport($_ENV["SMTP_HOST"], $_ENV["SMTP_PORT"]))
        ->setUsername($_ENV["SMTP_USER"])
        ->setPassword($_ENV["SMTP_PASSWORD"]);

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        // Create a message
        $message = (new Swift_Message('Wonderful Subject'))
        ->setFrom(['john@doe.com' => 'John Doe'])
        ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])
        ->setBody("Hi, you have a new message. Name: {$requestData['name']} Email: {$requestData['email']} Message: {$requestData['message']}");

        // Send the message
        $result = $mailer->send($message);
        return new RedirectResponse("/hoja-de-vida-php/contact");

    }

}


?>