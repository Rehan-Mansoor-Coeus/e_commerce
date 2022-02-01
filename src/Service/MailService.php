<?php
// src/Service/MessageGenerator.php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sendMail($to,$subject,$message,$mailer): string
    {
        $email = (new Email())
            ->from('rehan.mansoor@coeus-solutions.de')
            ->to($to)
            ->subject($subject)
            ->html($message);

        if($mailer->send($email)){
              return true;
        }else{
            $this->logger->error('Email is not sending ..');
            return true;
        }
    }
}
