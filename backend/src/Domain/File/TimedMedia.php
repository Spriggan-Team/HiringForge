<?php

namespace App\Domain\File;

use App\Domain\Exception\FileSizeExceeded;
use App\Domain\Exception\FileTimeExceeded;


class TimedMedia extends Media
{
    public int  $duration;    //stored in seconds,
    
    public function __construct(
        string $name,        // the name (a uniq generated one)
        float  $size,        //stored in bytes
        string $mime,        // th mime type of the file
        int  $duration,      //stored in seconds, can be equal to zero if object hydrated from bdd
        ?int $id = null,
        ?string $originalName = null,
        ?\DateTimeImmutable $createdAt = null,

    ){
        parent::__construct(
            id: $id,
            name: $name,
            size: $size,
            mime: $mime,
            originalName: $originalName,
            createdAt: $createdAt
        );
        $this->duration = $duration;
    }
    
    public static function hydrate(
        string $name,        // the name (a uniq generated one)
        float  $size,        //stored in bytes
        string $mime,        // th mime type of the file
        ?int $id ,
        ?string $originalName ,
        ?\DateTimeImmutable $createdAt ,
        int  $duration = 0,     //stored in seconds, !Warning can be equal to zero if object hydrated from bdd

    ){
        return new self(
            id: $id,
            name: $name,
            size: $size,
            mime: $mime,
            originalName: $originalName,
            createdAt: $createdAt,
            duration: $duration
        );
    }

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
        
        if($secondsLimitation &&  $this->duration > $secondsLimitation){
            throw new FileTimeExceeded(
                message: "Media's time too long",
                payload: new FileOriginalInfo($this->originalName)->toArray()
            );
        }

        if($sizeLimitation &&  $this->size > $sizeLimitation)
        {
            throw new FileSizeExceeded(
                message: "Media's size too large",
                payload: new FileOriginalInfo($this->originalName)->toArray()
            );
        }
    }

}