<?php

namespace App\Domain\JobOffer;

enum JobActivityStatus: string{
    case ACTIVE = "active";         //-- is associated to  at least one candidate
    case PENDING = "pending";       //-- all current candidate has been with
    case INACTIVE = "deactivate";   //-- no candidate at all since created
}