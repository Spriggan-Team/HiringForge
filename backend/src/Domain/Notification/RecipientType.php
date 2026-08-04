<?php


namespace App\Domain\Notification;


enum RecipientType: string{
    case ACCOUNT = 'ACCOUNT';
    case COMPANY = 'COMPANY';
}