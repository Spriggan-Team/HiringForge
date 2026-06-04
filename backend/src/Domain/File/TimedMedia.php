<?php

namespace App\Domain\File;

use App\Domain\Exception\FileExceedTime;
use App\Domain\Exception\FileSizeExceeded;
use App\Domain\Exception\FileTimeExceeded;

class TimedMedia
{
    public function __construct(
        public string $name,        // the name (a uniq generated one)
        public float  $size,        //stored in bytes
        public string $mime,        // th mime type of the file
        public int    $duration,    //stored in seconds,
        public ?string $originalName = null,
    ){}
    
    /**
     * Enforce time limitation and special type verification for a TimedMedia
     * @param string                 $type               #represents a specialisation of TimedMedia (audio ou video ...)
     * @param ?float                 $secondsLimitation  This is the duration of the timed media in seconds
     * @param ?float                 $sizeLimitation     the size limitation in bytes 
     * @throws \DomainException|FileTimeExceeded  
     * @return void
     */
    public function mustBe(string $type, ?float $secondsLimitation = null, ?float $sizeLimitation = null): void
    {
        if(!str_contains($this->mime, $type))
        {
            throw new \DomainException("Media Mime type mistmatch");
        }
        
        if($secondsLimitation && $secondsLimitation > $this->duration){
            throw new FileTimeExceeded(
                message: "Media's time too long",
                payload: new FileOriginalInfo($this->originalName)->toArray()
            );
        }

        if($sizeLimitation && $sizeLimitation > $this->size)
        {
            throw new FileSizeExceeded(
                message: "Media's size too large",
                payload: new FileOriginalInfo($this->originalName)->toArray()
            );
        }
    }

}