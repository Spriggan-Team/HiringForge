<?php


namespace App\Infrastructure\Persistence\Service;

use App\Domain\Shared\Service\EmbeddingProviderInterface;
use App\Domain\Shared\Service\VectorServiceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class QdrantVectorService implements VectorServiceInterface
{
    private string $qdrantUrl = "http://localhost:8000";

    public function __construct(
        private HttpClientInterface $httpClient,
        private EmbeddingProviderInterface $embeddingsProvider
    )
    {}

    /** Search for the closest skill ID matching the inputs text */
    public function searchClosestSkillId(string $text, float $threshold = 0.88): ?int
    {
        $vector = $this->embeddingsProvider->generateEmbedding($text);
        if(empty($vector)){
            return null;
        }

        //-- send request to Qdrant Vector Engine
        $response = $this->httpClient->request("POST", $this->qdrantUrl . '/collections/skills/points/search',[
            'json' => [
                'vector' => $vector,
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
    public function indexSkill(int $skillId, string $skillName) : void
    {
        $vector = $this->embeddingsProvider->generateEmbedding($skillName);
        if(empty($vector)){
            return;
        }    

        $this->httpClient->request("PUT", $this->qdrantUrl . "/collections/skills/points", [
            'json' => [
                'points' => [
                    [
                        'id' => $skillId,
                        'vector' => $vector
                    ]
                ]
            ]
        ]);
    }
}