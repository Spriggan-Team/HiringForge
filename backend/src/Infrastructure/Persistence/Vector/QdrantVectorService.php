<?php


namespace App\Infrastructure\Persistence\Vector;

use App\Domain\Shared\Service\EmbeddingProviderInterface;
use App\Domain\Shared\Service\VectorServiceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class QdrantVectorService implements VectorServiceInterface
{
    private string $qdrantRootUrl;

    public function __construct(
        string $qdrantRootUrl,
        private HttpClientInterface $httpClient,
        private EmbeddingProviderInterface $embeddingsProvider,
    )
    {
        $this->qdrantRootUrl = $qdrantRootUrl;
    }

    /** Search for the closest skill ID matching the inputs text */
    public function searchClosestSkillId(string $text, float $threshold = 0.88): ?int
    {
        $vector = $this->embeddingsProvider->generateEmbedding($text);
        if(empty($vector)){
            return null;
        }

        //-- send request to Qdrant Vector Engine
        $response = $this->httpClient->request(
            "POST", 
            $this->qdrantRootUrl . '/collections/skills/points/query',
            [
            'json' => [
                'query' => $vector,
                "limit" => 1,
                "score_threshold" => $threshold // Filterss similarity >= 0.88 directly
            ]
        ]);

        $data = $response->toArray();
        $results = $data["result"] ?? [];

        if(!empty($results)){
            return (int) $results[0]["id"];
        }

        return null;
    }


    /**
     * Index or update a Skill vector in the external Vector DB
     */
    public function indexSkill(string $skillId, string $skillName, ?array $vector = null) : void
    {
        $vec = $vector ?? $this->embeddingsProvider->generateEmbedding($skillName);
        if(empty($vec)){
            return;
        }    

        $response = $this->httpClient->request("PUT", $this->qdrantRootUrl . "/collections/skills/points", [
            'json' => [
                'points' => [
                    [
                        'id' => $skillId,
                        'vector' => $vec
                    ]
                ]
            ]
        ]);

        $response->getStatusCode();
    }
}