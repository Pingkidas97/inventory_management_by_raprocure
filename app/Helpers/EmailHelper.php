<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;
use App\Mail\CustomMail;
use App\Models\EmailTemplate; // Use the EmailTemplate model

class EmailHelper
{
    public static function sendMail($to, $subject, $body, $mailer='smtp',$cc = [], $bcc = [], $attachments = [])
    {
        try {
            // Prepare the email data
            $view = 'emails.custom-template';  // Use a generic view or create one
            // Send the email
            Mail::mailer($mailer)->to($to)
            ->send(new CustomMail(
                $subject,   // Subject
                $view,     // Blade view
                ['body' => $body],// Data to pass to the view
                $attachments, // Attachments
                $cc ,     // CC addresses
                $bcc     // BCC addresses
            ));
            // Return success message
            return 'Email sent successfully!';
        } catch (\Exception $e) {
            // Return error message
            return 'Error sending email: ' . $e->getMessage();
        }
    }

    public static function isValidEmail(string $email): bool
    {
        // Check valid format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Extract domain
        $domain = substr(strrchr($email, "@"), 1);

        // Check if domain has MX record
        return checkdnsrr($domain, "MX");
    }
}
