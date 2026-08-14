<?php


namespace App\Domain\Shared\Document;

final readonly class ExtractedPage
{
    public function __construct(
        public int $number,
        public string $content,
    ) {}
}