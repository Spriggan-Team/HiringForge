<?php

namespace App\Domain\Candidate\Application;

use App\Domain\Shared\Document\ExtractedDocument;

interface ResumeLexicalParserInterface
{
    /**
     * Clearing, normalizing, lexical parsing text
     */
    public function parse(ExtractedDocument  $document): ExtractedDocument;
}