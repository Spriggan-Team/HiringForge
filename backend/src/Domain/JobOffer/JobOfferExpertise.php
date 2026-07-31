<?php

namespace App\Domain\JobOffer;

enum JobOfferExpertise: string{
    case ACTIVE = "active";         //-- is associated to  at least one candidate
    case PENDING = "pending";       //-- all current candidate has been with

    case INTERN = "intern";
    case JUNIOR = "junior";
    case MID = "mid";
    case CONFIRMED = "confirmed";
    case SENIOR = "senior";
    case LEAD = "lead";
    case STAFF = "staff";
    case PRINCIPAL = "principal";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}