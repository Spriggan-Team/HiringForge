<?php

namespace App\Domain\File;

/**
* This class implements functions that take in an object and convert it into domain item.
* It is responsible to build the bridge between the file infrastructure and the domain related rules.
*/
interface MediaFactoryInterface
{
    /**
     * This function tell if a file is a static media (image, document) or not
     * @param mixed     $file        The file you want to evaluate
     * @throws \Exception
     */
    public function isTimedMedia(mixed $file): bool;

    /**
     * This function convert a static file (such as images and documents...) into a domain file  object
     * that can enforces specific rules
     * @param mixed $file
     * @throws \DomainException  should be returned when the file is not a static media (image, doc ...ect)
     * @return StaticMedia       The corresponding domain object
     */
    public function createStaticMedia(mixed $file): StaticMedia;

    /**
     * This function convert a timed file (such as video and audio...) into a domain file object
     * that can enforces specific rules
     *  @param mixed $file           The file class/type you used for managing your backend
     *  @throws \DomainException     should be returned when the file is not a timed media (video, audio ...ect)
     *  @return TimedMedia           The corresponding domain object
    */
    public function createTimedMedia(mixed $file): TimedMedia;
    
}