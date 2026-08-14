<?php


namespace App\Domain\Shared\Services;

interface EmbeddingProviderInterface
{
    public function generateEmbedding(string $prompt): array;
}