<?php

namespace App\Application\Usecases;


/**
 * !IMPORTANT: is currently used
 * 
 * Represents the result of an attempted file upload.
 * 
 * Stores the original name of the uploaded file and an optional 
 * message describing success, failure, or any additional info.
 */
class UploadedFileInfo
{
    public function __construct(
        public string $originalName,
        public string $message
    ){}
}
