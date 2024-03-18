<?php

namespace App\Service;

use SendGrid\Mail\Mail;
use SendGrid;

class EmailService
{
    public function sendEmail(string $toEmail): void
    {


        $email = new Mail(); 
        $email->setFrom("lea.sureau@ynov.com", "Example User");
        $email->setSubject("Sending with SendGrid is Fun");
        $email->addTo($toEmail, "Example User");
        $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
        $email->addContent(
            "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
        );
        $sendgrid = new SendGrid('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');
        try {
            $response = $sendgrid->send($email);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }
}   
}
