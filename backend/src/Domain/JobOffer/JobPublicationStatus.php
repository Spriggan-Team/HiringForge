<?php

namespace App\Domain\JobOffer;

enum JobPublicationStatus: string
{
    case DRAFT = 'draft';           // Is the initiate state of an offer
    case CLOSED = 'closed';         // means the data is deleted or not longer use
    
    case PUBLISHED = 'published';   // publish it to all user

    public function canTransition(self $newStatus): bool
    {
        return match ($this) {
            self::DRAFT => in_array($newStatus, [self::PUBLISHED, self::CLOSED], true),
            self::PUBLISHED => in_array($newStatus, [self::DRAFT, self::CLOSED], true),
            self::CLOSED => false, // Final
        };
    }
}