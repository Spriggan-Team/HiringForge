<?php

namespace App\Infrastructure\Email;

use App\Domain\Email\EmailCategory;
use App\Domain\Email\EmailMessage;
use PHPMailer\PHPMailer\PHPMailer;
use App\Domain\Email\EmailServicesInterface;

use DOMDocument;
use Override;


class EmailServices implements EmailServicesInterface
{
    public function __construct(
        private string $SMTP_HOST,
        private string $MAIL_USER_NAME,
        private string $MAIL_USER_PASSWORD,
        public PHPMailer $mail = new PHPMailer(),
    ){
        $this->mail->isSMTP();
        $this->mail->Host = $this->SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->MAIL_USER_NAME;
        $this->mail->Password = $this->MAIL_USER_PASSWORD;

        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        
        // - Ensure utf8
        $this->mail->CharSet = PHPMailer::CHARSET_UTF8; 
    }

    #[Override]
    public function prepareEmail(EmailMessage $emailMessage): DOMDocument
    {
        //-- select template
        $htmlTemplate = null;
        if ($emailMessage->type === EmailCategory::WARNING) {
            $htmlTemplate = file_get_contents(__DIR__ . "/Templates/warning.html");
        } else {
            $htmlTemplate = file_get_contents(__DIR__ . "./Templates/default.html");
        }

        //-- Interpolation
        $variables = [
            '{{ title }}' => $emailMessage->title,
            '{{ description }}' => $emailMessage->description,
            '{{ code }}' => $emailMessage->code
        ];

        $htmlFinal = str_replace(array_keys($variables), array_values($variables), $htmlTemplate);

        //-- convert interpolated string into domDocument
        $document = new DOMDocument();
        
        //-- xml header
        @$document->loadHTML('<?xml encoding="utf-8" ?>' . $htmlFinal, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $document;
    }


    #[Override]
    public function sendTo(string $receiver, EmailMessage $emailMessage): void
    {
        $this->mail->addAddress($receiver);
        $this->mail->Subject = $emailMessage->title;
        
        //--retreive dom
        $document = $this->prepareEmail($emailMessage);
        
        // Injetc generated html into phph mailer
        // msgHTML() convertit le document en texte brut alternatif automatiquement
        $this->mail->msgHTML($document->saveHTML());

        $this->mail->send();
        
        // Pense à vider les adresses si ton service reste en mémoire pour plusieurs envois
        $this->mail->clearAddresses();
    }
}