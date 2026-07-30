<?php

namespace App\Domain\Shared\Service;

/**
 * Read or write using a vector database service (ex: chromadb, qdrant)
 */
interface VectorServiceInterface
{
    /**
     * @return array{skill_id:string, score:float}|null
     */
    public function searchClosestSkill(
        string $text,
        float $threshold = 0.88
    ): ?array;
    
    public function indexSkill(string $skillId, string $skillName, ?array $vector = null) : void;

    /** 
     * Finds the nearest skill directly from a float[] array.
     *
     * @param array<int, float> $vector
     * @return array{skill_id: string, score: float}|null
     */
    public function searchClosestSkillByVector(array $vector, float $threshold = 0.88): ?array;
}