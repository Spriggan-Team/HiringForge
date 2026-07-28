<?php

namespace App\Domain\Shared\Service;

/**
 * Read or write using a vector database service (ex: chromadb, qdrant)
 */
interface VectorServiceInterface
{
    public function searchClosestSkillId(string $text, float $threshold = 0.88): ?int;
    
    public function indexSkill(string $skillId, string $skillName) : void;
}