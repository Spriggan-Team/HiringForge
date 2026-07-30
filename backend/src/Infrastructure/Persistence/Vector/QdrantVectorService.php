<?php


namespace App\Infrastructure\Persistence\Vector;

use App\Domain\Shared\Service\EmbeddingProviderInterface;
use App\Domain\Shared\Service\VectorServiceInterface;
use Override;
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

    /**
     * @return array{skill_id:string, score:float}|null
     */
    public function searchClosestSkill(
        string $text,
        float $threshold = 0.88
    ): ?array
    {
        $vector = $this->embeddingsProvider->generateEmbedding($text);

        if (empty($vector)) {
            return null;
        }

        $response = $this->httpClient->request(
            "POST",
            $this->qdrantRootUrl . '/collections/skills/points/query',
            [
                'json' => [
                    'query' => $vector,
                    'limit' => 1,
                    'score_threshold' => $threshold
                ]
            ]
        );

        $data = $response->toArray();

        $results = $data["result"] ?? [];

        if (empty($results)) {
            return null;
        }

        $point = $results[0];

        return [
            'skill_id' => (string) $point['payload']['skill_id'],
            'score'    => (float) $point['score'],
        ];
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
                        'id' => crc32($skillId),
                        'vector' => $vec,
                        'payload' => [
                            'skill_id' => $skillId
                        ],
                    ]
                ]
            ]
        ]);

        $response->getStatusCode();
    }

    

    #[Override]
    /** 
     * Finds the nearest skill directly from a float[] array.
     *
     * @param array<int, float> $vector
     * @return array{skill_id: string, score: float}|null
     */
    public function searchClosestSkillByVector(array $vector, float $threshold = 0.88): ?array
    {
        if (empty($vector)) {
            return null;
        }

        $response = $this->httpClient->request(
            'POST',
            $this->qdrantRootUrl . '/collections/skills/points/query',
            [
                'json' => [
                    'query' => $vector,
                    'limit' => 1,
                    'score_threshold' => $threshold,
                    'with_payload' => true,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = $response->toArray(false);
        $points = $data['result']['points'] ?? $data['result'] ?? [];

        if (empty($points)) {
            return null;
        }

        $closestPoint = $points[0];

        if (!isset($closestPoint['payload']['skill_id'])) {
            return null;
        }

        return [
            'skill_id' => (string) $closestPoint['payload']['skill_id'],
            'score'    => (float) $closestPoint['score'],
        ];
    }
}