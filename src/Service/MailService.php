<?php
// src/Service/MessageGenerator.php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param $mailer
     * @return bool
     */
    public function sendMail($to, $subject, $message, $mailer): bool
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
