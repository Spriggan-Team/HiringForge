<?php


namespace App\Domain\Shared\Service;

interface EmbeddingProviderInterface
{
    public function generateEmbedding(string $prompt): array;
}