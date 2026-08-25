<?php

namespace App\Infrastructure\Services\AI\Resume;

use App\Domain\Candidate\Application\CVParserInterface;
use App\Domain\Candidate\Application\ResumeAiParserInterface;
use App\Domain\Candidate\Application\ResumeLexicalParserInterface;
use App\Domain\Candidate\Application\StructuredResume;
use App\Domain\Shared\Document\DocumentExtractorInterface;
use Override;


class CVParser implements CVParserInterface
{
    public function __construct(
        private DocumentExtractorInterface $docExtractor,
        private ResumeAiParserInterface $resumeAiParser,
        private ResumeLexicalParserInterface $resumeLexicalParser,
    ){}


    #[Override]
    public function parse(string $absoluteFilePath): StructuredResume
    {
        $plainText = $this->docExtractor->extract(
            absolutePath: $absoluteFilePath,
        );

        // -------------------------------------------------
        // Lexical parsing
        // -------------------------------------------------

        $clearDocument  = $this->resumeLexicalParser->parse($plainText);
        
        // -------------------------------------------------
        // AI parsing
        // -------------------------------------------------
        
        return $this->resumeAiParser->parse($clearDocument);
    }
}