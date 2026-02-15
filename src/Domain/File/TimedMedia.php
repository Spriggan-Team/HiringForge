<?php

namespace App\Domain\File;

class TimedMedia
{
    public function __construct(
        public string $name,        // the name (a uniq generated one)
        public float  $size,        //stored in bytes
        public string $mime,        // th mime type of the file
        public int    $duration,    //stored in seconds
    ){}
    
    /**
     * Enforce time limitation and special type verification for a TimedMedia
     * @param string                 $type               #represents a specialisation of TimedMedia (audio ou video ...)
     * @param ?float                 $secondsLimitation  This is the duration of the timed media in seconds
     * @param ?float                 $sizeLimitation     the size limitation in bytes 
     * @throws \DomainException     
     * @return void
     */
    public function mustBe(string $type, ?float $secondsLimitation = null, ?float $sizeLimitation = null): void
    {
        if(!str_contains($this->mime, $type))
        {
            throw new \DomainException("Media Mime type mistmatch");
        }
        
        if($secondsLimitation && $secondsLimitation > $this->duration){
            throw new \DomainException("Media's time too long");
        }

        if($sizeLimitation && $sizeLimitation > $this->size)
        {
            throw new \DomainException("Media's size too large");
        }
    }

}