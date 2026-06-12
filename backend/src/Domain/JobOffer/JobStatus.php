<?php

namespace App\Domain\JobOffer;

enum JobStatus: string
{
    case ACTIVE = "active";         //-- is associated to  at least one candidate
    case PENDING = "pending";       //-- all current candidate has been with

    case DRAFT = 'draft';           // Is the initiate state of an offer
    case CLOSED = 'closed';         // means the data is deleted or not longer use
    
    case PUBLISHED = 'published';   // publish it to all user
}