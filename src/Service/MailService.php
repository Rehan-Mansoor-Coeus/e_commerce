<?php
// src/Service/MessageGenerator.php
namespace App\Service;

use Symfony\Component\Mime\Email;

class MailService
{
    public function sendMail($to,$subject,$message,$mailer): string
    {
        $email = (new Email())
            ->from('rehan.mansoor@coeus-solutions.de')
            ->to($to)
            ->subject($subject)
            ->html($message);

        $mailer->send($email);
        return true;
    }
}
