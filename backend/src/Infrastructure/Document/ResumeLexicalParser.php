<?php

namespace App\Infrastructure\Document;


use App\Domain\Candidate\Application\ResumeLexicalParserInterface;
use App\Domain\Shared\Document\ExtractedDocument;
use App\Domain\Shared\Document\ExtractedPage;
use Override;




final class ResumeLexicalParser implements ResumeLexicalParserInterface
{
    #[Override]
    public function parse(ExtractedDocument  $document): ExtractedDocument
    {
        $pages = array_map(
            fn(ExtractedPage $page) => new ExtractedPage(
                number: $page->number,
                content: $this->parsePage($page->content)
            ),
            $document->pages
        );

        return new ExtractedDocument($pages);
    }


    private function parsePage(string $text){
        $text = $this->normalizeLineEndings($text);
        $text = $this->removeControlCharacters($text);
        $text = $this->normalizeSpaces($text);
        $text = $this->repairBrokenWords($text);
        $text = $this->normalizeEmptyLines($text);

        return trim($text);
    }



    private function normalizeLineEndings(string $text): string
    {
        return str_replace(
            ["\r\n", "\r"],
            "\n",
            $text
        );
    }


    private function removeControlCharacters(string $text): string
    {
        return preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $text
        );
    }


    private function normalizeSpaces(string $text): string
    {
        // NBSP →  normal space
        $text = str_replace("\u{00A0}", ' ', $text);

        // n spaces/tabs → 1 espace
        return preg_replace(
            '/[ \t]+/u',
            ' ',
            $text
        );
    }

    private function repairBrokenWords(string $text): string
    {
        // "expé-\nrience" → "expérience"
        return preg_replace(
            '/(\p{L})-\n(\p{L})/u',
            '$1$2',
            $text
        );
    }

    private function normalizeEmptyLines(string $text): string
    {
        // Removes unnecessary spaces around line breaks
        $text = preg_replace(
            '/[ \t]*\n[ \t]*/u',
            "\n",
            $text
        );

        // A maximum of two consecutive line breaks
        return preg_replace(
            "/\n{3,}/u",
            "\n\n",
            $text
        );
    }
}