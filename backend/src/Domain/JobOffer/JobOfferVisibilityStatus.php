<?php

namespace App\Domain\JobOffer;

enum JobOfferVisibilityStatus: string
{
    case PRIVATE = 'private';         // means the data is deleted or not longer use
    case PUBLIC = 'public';   // publish it to all user
}