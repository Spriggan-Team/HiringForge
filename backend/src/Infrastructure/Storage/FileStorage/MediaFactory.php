<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;
use App\Domain\File\MediaFactoryInterface;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
* This class implements functions that take in an object and convert it into domain item.
* It is responsible to build the bridge between the file infrastructure and the domain related rules.
*/
class MediaFactory implements MediaFactoryInterface
{
    private \getID3 $analyser;
    
    public function __construct()
    {
        $this->analyser = new \getID3();
    }
    
    /**
     * This function tell if a file is a timed media (video, audio ...) or not
     * @param mixed     $file        The file you want to evaluate, The file class/type you used for managing your backend
     * @throws \Exception
     */
    public function isTimedMedia(mixed $file): bool
    {
        if($file instanceof UploadedFile){
            $mime = mime_content_type($file->getPathname());
            return str_starts_with($mime, 'video/') || str_starts_with($mime, 'audio/');
        }
        throw new \Exception("Such a class of file is not supported yet!!");
    }

    /**
     * This function convert a static file (such as images and documents...) into a domain file  object
     * that can enforces specific/buisness rules
     * @param mixed $file                        The file class/type  used for managing file on your app
     * 
     * @throws \DomainException|FileSizeExceeded|\Exception       This can be thrown if the file you attempt to parse can't be parsed
     *                                           or the specific class of file you are using are still not supported yet!!
     * 
     * @return StaticMedia                       The corresponding domain object, it should be used to enforce domain rules
     */
    public function createStaticMedia(mixed $file): StaticMedia
    {
        if(!$this->isTimedMedia($file))
        {
            $static = new StaticMedia(
                name:  Uuid::uuid4()->toString() . '.' . $file->guessExtension(),
                size: $file->getSize() , //store in bytes
                mime: $file->getMimeType()
            );
            return $static;
        }
        throw new \DomainException("You mustn't try to pasrse a timed media as a static one");
    }

    
    /**
     * This function convert a timed file (such as video and audio...) into a domain file object
     * that can enforces specific rules
     * @return TimedMedia           The corresponding domain object
    */
    public function createTimedMedia(mixed $file): TimedMedia
    {
        if($this->isTimedMedia($file))
        {
            $info = $this->analyser->analyze($file->getPathname());
            if(!isset($info['playtime_seconds']) || $info['playtime_seconds'] <= 0)
            {
                throw new \DomainException(
                    sprintf('[File] %s is not a valid timed media', $file->getClientOriginalName() ?? 'unknown')
                );
            }
            $timed = new TimedMedia(
                name: Uuid::uuid4()->toString() . '.' . $file->guessExtension(),
                size: $file->getSize(),
                mime: $file->getMimeType(), //store in bytes
                duration: $info['playtime_seconds']
            );
            return $timed;
        }
        throw new \DomainException("Your file must mactch with a timed one (ex: video, audio...ect)");
    }
}