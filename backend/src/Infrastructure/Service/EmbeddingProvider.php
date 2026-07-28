<?php

namespace App\Infrastructure\Service;

use Override;
use App\Domain\Shared\Service\EmbeddingProviderInterface;
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

    /**
     * @throws \Exception
     */
    #[Override]
    public function generateEmbedding(string $prompt): array
    {
        try{
            $response = $this->httpClient->request(
                "POST",
                $this->ollamaRootUrl . "/api/embeddings",
                [
                    'json' => [
                        "model" => $this->ollamaEmbedderModel,
                        "prompt" => $prompt
                    ]
                ]
            );
            $data = $response->toArray();
            
            return $data["embedding"];
        }
        catch(\Exception $exception)
        {
            throw $exception;
        }
    }
}