<?php

namespace App\Domain\File;

use App\Domain\Exception\FileSizeExceeded;

/**
 * This is a DTO only used for define static media schema
 */
class StaticMedia
{
    public function __construct(
        /** @var string a uniq name used for uploading */
        public string $name,
        /** @var float $size represents the size of the file in bytes */
        public float  $size,
        /** @var string $mime here is the mime type */
        public string $mime,
    ){}

    /**
     * This function ensure size & type validation for a given StaticMedia object
     * @param  float  $sizeLimitation  (bytes)    The file size limitation to enforce. The size of the given object must not be greater than the limitation (should be <=)
     * @param  string $mime                       Optionnal, it is used in specific case you want to target a special mime
     * @throws \DomainException|FileSizeExceeded                     An exception that occurs when the rule above are not respected
     * @return void
     */
    public function mustBe(float $sizeLimitation, ?string $mime = null): void
    {
        if($sizeLimitation > $this->size)
        {
            throw new FileSizeExceeded('File too large');
        }

        if($mime && $this->mime != $mime)
        {
            throw new \DomainException("Mime type mismatch");
        }
    }

}