<?php

namespace App\Infrastructure\Services;

use Override;
use App\Domain\Shared\Services\EmbeddingProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmbeddingProvider implements EmbeddingProviderInterface
{
    private string $ollamaRootUrl;
    private string $ollamaEmbedderModel;

    public function __construct(
        string $ollamaRootUrl,
        private HttpClientInterface $httpClient,
        string $ollamaEmbedderModel = "bge-m3",
    ){
        $this->ollamaEmbedderModel = $ollamaEmbedderModel;
        $this->ollamaRootUrl = $ollamaRootUrl;
    }

    #[Override]
    public function generateEmbedding(string $prompt): array
    {
        $response = $this->httpClient->request(
            'POST',
            $this->ollamaRootUrl . '/api/embed',
            [
                'json' => [
                    'model' => $this->ollamaEmbedderModel,
                    'input' => $prompt,
                ],
            ]
        );

        $data = $response->toArray();

        if (!isset($data['embeddings'][0])) {
            throw new \RuntimeException('No embedding returned by Ollama.');
        }

        return $data['embeddings'][0];
    }
}