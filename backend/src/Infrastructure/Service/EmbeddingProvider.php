<?php

namespace App\Infrastructure\Service;

use App\Domain\Shared\Service\EmbeddingProviderInterface;
use Override;

class EmbeddingProvider implements EmbeddingProviderInterface
{
    #[Override]
    public function generateEmbedding(string $name): string
    {
        throw new \Exception('Not implemented');
    }
}