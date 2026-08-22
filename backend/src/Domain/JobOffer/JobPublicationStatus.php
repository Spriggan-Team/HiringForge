<?php

namespace App\Domain\JobOffer;

enum JobPublicationStatus: string
{
    case DRAFT = 'draft';           // Is the initiate state of an offer
    case CLOSED = 'closed';         // means the data is deleted or not longer use
    
    case PUBLISHED = 'published';   // publish it to all user
}