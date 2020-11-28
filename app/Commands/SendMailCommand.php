<?php

// La estructira completa de esta clase se encuentra en la documentación de Symfony Commands: https://symfony.com/doc/current/console.html

namespace App\Commands;

use App\Models\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class SendMailCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:send-mail';

    protected function execute(InputInterface $input, OutputInterface $output) {

        $pendingMessage = Message::where("sent", false)->first();

        if ($pendingMessage) {

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
            ->setBody("Hi, you have a new message. Name: {$pendingMessage->name} Email: {$pendingMessage->email} Message: {$pendingMessage->message}");
    
            // Send the message
            $result = $mailer->send($message);

            $pendingMessage->sent =true;
            $pendingMessage->save();

        }


        return Command::SUCCESS;

    }
}

?>