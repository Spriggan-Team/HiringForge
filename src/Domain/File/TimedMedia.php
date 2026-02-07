<?php

namespace App\Domain\File;

class TimedMedia
{
    public function __construct(
        public string $name,
        public float  $size,
        public string $mime,
        public int  $duration,
    ){}
    
        /**
     * Enforce time limitation and special type verification for a TimedMedia
     * @param TimedMediaType        $type  #represents a specialisation of TimedMedia (audio ou video ...)
     * @throws \DomainException
     * @return void
     */
    public function mustBe(TimedMediaType $type, ?int $timeLimitation = null, ?int $sizeLimitation = null): void
    {
        //TODO: Implement
    }
}