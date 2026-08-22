<?php


namespace App\Domain\Candidate\Application;

use App\Domain\Shared\Document\ExtractedDocument;


interface ResumeAiParserInterface
{
    /**
     * @throws \Exception|\RuntimeException
     */
    public function parse(ExtractedDocument $document): StructuredResume;
}