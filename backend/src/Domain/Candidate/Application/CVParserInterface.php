<?php


namespace App\Domain\Candidate\Application;


interface CVParserInterface
{
    public function parse(string $absoluteFilePath): StructuredResume;
}