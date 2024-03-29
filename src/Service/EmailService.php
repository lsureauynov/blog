<?php

namespace App\Service;

use SendGrid\Mail\Mail;
use SendGrid;

class EmailService
{
    private string $sendGridApiKey;

    public function __construct(string $sendGridApiKey)
    {
        $this->sendGridApiKey = $sendGridApiKey;
    }

    public function sendEmail(string $toEmail, string $templateId): void
    {
        $email = new Mail(); 
        $email->setFrom("lea.sureau@ynov.com", "Blog");
        $email->setSubject("Sending with SendGrid is Fun");
        $email->addTo($toEmail, "Newcomer!");
        $email->setTemplateId($templateId); 

        $sendgrid = new SendGrid($this->sendGridApiKey);
        try {
            $response = $sendgrid->send($email);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo "An error occured";
        }
    }   
}
