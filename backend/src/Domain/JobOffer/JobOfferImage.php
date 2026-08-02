<?php

namespace App\Domain\JobOffer;

use App\Domain\File\StaticMedia;

class JobOfferImage
{
    public function __construct(
        public StaticMedia $media,
        public bool $isMain = false,       // Important: indicates if an image of a job offer is main 
    )
    {}

    public static function create(
        StaticMedia $media,
        bool $isMain = false,
    ){
        return new self(media: $media, isMain: $isMain);
    }
}