<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;

/**
* This class implements functions that take in an object and convert it into domain item.
* It is responsible to build the bridge between the file infrastructure and the domain related rules.
*/
class FileUtils
{
    /**
     * This function tell if a file is a static media (image, document) or not
     * @param mixed     $file        The file you want to evaluate
     */
    public function isTimedMedia(mixed $file)
    {
        //TODO: implement
        throw new \DomainException("Check if the file is a media");
    }

    /**
     * This function convert a static file (such as images and documents...) into a domain file  object
     * that can enforces specific rules
     * @return StaticMedia  The corresponding domain object
     */
    public function parseAsStaticMedia(): StaticMedia
    {
        //TODO: implement
        throw new \DomainException("Please impelment the parseAsStaticMedia of utils file");
    }

    /**
     * This function convert a timed file (such as video and audio...) into a domain file object
     * that can enforces specific rules
     * @return TimedMedia           The corresponding domain object
    */
    public function parseAsTimedMedia(): TimedMedia
    {
        //TODO: impelemnt
        throw new \DomainException("Please impelment the parseAsTimeMedia of utils file");
    }
}