<?php

namespace App\Domain\Shared\Services;

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
    

    /**
     * Insert a new skill into vector bdd
     */
    public function indexSkill(string $skillId, string $skillName, ?array $vector = null) : void;

    /** 
     * Finds the nearest skill directly from a float[] array.
     *
     * @param array<int, float> $vector
     * @return array{skill_id: string, score: float}|null
     */
    public function searchClosestSkillByVector(array $vector, float $threshold = 0.88): ?array;


    /**
     * Ensure a collectio exits in the vec bdd
     * If does not then it is  created 
     */
    public function ensureCollectionExists(string $collection = "skills"): void;
}