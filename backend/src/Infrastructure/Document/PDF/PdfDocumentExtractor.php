<?php


namespace App\Infrastructure\Document\PDF;



use App\Domain\Exception\UnsupportedDocumentException;
use App\Domain\Shared\Document\DocumentExtractorInterface;
use App\Domain\Shared\Document\ExtractedDocument;
use App\Domain\Shared\Document\ExtractedPage;


use InvalidArgumentException;

use Smalot\PdfParser\Parser;


class PdfDocumentExtractor implements DocumentExtractorInterface
{

    public function __construct(
        private readonly Parser $parser,
    ) {}

    public  function extract(string $absolutePath): ExtractedDocument
    {
        if(!is_file($absolutePath)){
            throw new InvalidArgumentException(
                sprintf('File does not exist: "%s".', $absolutePath)
            );
        }

        if(!is_readable($absolutePath)){
            throw new InvalidArgumentException(
                sprintf('File is not readable: "%s".', $absolutePath)
            );
        }

        $mimeType = mime_content_type($absolutePath);

        if ($mimeType === false) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to determine MIME type of "%s".',
                    $absolutePath
                )
            );
        }
        
        if ($mimeType !== 'application/pdf') {
            throw new UnsupportedDocumentException(
                sprintf(
                    'Expected a PDF document, got "%s".',
                    $mimeType
                )
            );
        }


        $document = $this->parser->parseFile($absolutePath);
        $pages = [];

        foreach($document->getPages() as $index => $page){
            $pages[] = new ExtractedPage(
                number: $index +1,
                content: $page->getText()
            );
        }

        return new ExtractedDocument($pages);
    }
}