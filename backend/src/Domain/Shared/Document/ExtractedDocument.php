<?php

namespace App\Domain\Shared\Document;


final readonly class ExtractedDocument
{
    /**
     * @param ExtractedPage[] $pages
     */
    public function __construct(
        public array $pages,
    ) {}

    public function text(): string
    {
        return implode(
            "\n\n",
            array_map(
                fn (ExtractedPage $page) => $page->content,
                $this->pages
            )
        );
    }
}