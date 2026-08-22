<?php

namespace App\Domain\Shared\Document;

use App\Domain\File\FileType;
use App\Domain\Exception\UnsupportedDocumentException;


interface DocumentExtractorInterface
{
    /**
     * @throws \Exception|\InvalidArgumentException|UnsupportedDocumentException
     */
    public  function extract(string $absolutePath): ExtractedDocument;
}