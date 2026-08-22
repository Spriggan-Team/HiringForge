<?php

namespace App\Infrastructure\Email;

use App\Domain\Email\EmailCategory;
use App\Domain\Email\EmailMessage;
use App\Domain\Email\EmailServicesInterface;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

use DOMDocument;
use Override;


class EmailServices implements EmailServicesInterface
{
    public function __construct(
        private string $SMTP_HOST,
        private string $MAIL_USER_NAME,
        private string $MAIL_USER_PASSWORD,
        public PHPMailer $mail = new PHPMailer(true),
    ){
        $this->mail->isSMTP();
        $this->mail->Host = $this->SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->MAIL_USER_NAME;
        $this->mail->Password = $this->MAIL_USER_PASSWORD;

        $this->mail->Port = 587;
        $this->mail->Timeout = 5;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        //-- debug
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF; 
        
        //-- config sender
        $this->mail->setFrom($this->MAIL_USER_NAME, 'DigitalCop');
        
        //-- ensure special chars transmission
        $this->mail->CharSet = PHPMailer::CHARSET_UTF8; 
    }

    #[Override]
    public function prepareEmail(EmailMessage $emailMessage): DOMDocument
    {
        // -- HTML Selection 
        $htmlTemplate = null;
        if ($emailMessage->type === EmailCategory::WARNING) {
            $htmlTemplate = file_get_contents(__DIR__ . "/Templates/warning.html");
        }
        //-- default
        else {
            $htmlTemplate = file_get_contents(__DIR__ . "/Templates/default.html");
        }

        // -- Interpolate variables
        $variables = [
            '{{ title }}' => $emailMessage->title,
            '{{ description }}' => $emailMessage->description,
            '{{ code }}' => $emailMessage->code
        ];

        $htmlFinal = str_replace(array_keys($variables), array_values($variables), $htmlTemplate);

        // -- convert into dom doc
        $document = new DOMDocument();
        
        //--read xml with charset utf8
        @$document->loadHTML('<?xml encoding="utf-8" ?>' . $htmlFinal, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $document;
    }

    #[Override]
    public function sendTo(string $receiver, EmailMessage $emailMessage): void
    {
        try {
            $this->mail->addAddress($receiver);
            $this->mail->Subject = $emailMessage->title;
            
            // -- DOM generation
            $document = $this->prepareEmail($emailMessage);
            $html = $document->saveHTML();
            
            //-- inject html template
            $this->mail->msgHTML($html);

            //--sent mail
            $this->mail->send();
            
        }
        catch (\Exception $e) {
            error_log("Erreur critique PHPMailer : " . $this->mail->ErrorInfo . " | Message: " . $e->getMessage());
            throw $e; 
        }
        finally {
            $this->mail->clearAddresses();
        }
    }


    public function setSMTPDebug(int $mode): self
    {
        $this->mail->SMTPDebug = $mode;
        return $this;
    }
}