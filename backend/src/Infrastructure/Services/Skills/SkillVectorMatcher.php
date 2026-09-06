<?php 

namespace App\Infrastructure\Services\Skills;

use App\Domain\Shared\Services\EmbeddingProviderInterface;
use App\Domain\Shared\Services\VectorServiceInterface;
use App\Domain\Shared\Services\AiValidatorServiceInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;

use Doctrine\ORM\EntityManagerInterface;


/***
 * Helper for skill vector action
 */
class SkillVectorMatcher
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ?VectorServiceInterface $vectorService = null,
        private readonly ?EmbeddingProviderInterface $embeddingProvider = null,
        private readonly ?AiValidatorServiceInterface $aiValidator = null
    ) {}

    public function ensureColectionExist(string $collection = "skills"){
        $this->vectorService->ensureCollectionExists($collection);
    }


    public function generateEmbedding(string $name): ?array
    {
        return $this->embeddingProvider?->generateEmbedding($name);
    }


    public function findMatchingConcept(string $name, array $vector, bool $iaValidation = true): ?SkillEntity
    {
        if (!$this->vectorService) {
            return null;
        }

        $result = $this->vectorService->searchClosestSkillByVector($vector, 0.88);
        if (!$result) {
            return null;
        }

        /** @var SkillEntity|null $candidateSkill */
        $candidateSkill = $this->em->getRepository(SkillEntity::class)->find($result['skill_id']);
        if (!$candidateSkill) {
            return null;
        }

        if ($iaValidation && $this->aiValidator) {
            $isValid = $this->aiValidator->isSameSkillConcept($name, $candidateSkill->getCanonicalName());
            if (!$isValid) {
                return null;
            }
        }

        return $candidateSkill;
    }


    public function indexSkill(string $skillId, string $canonicalName, array $vector): void
    {
        $this->vectorService?->indexSkill($skillId, $canonicalName, $vector);
    }


    /**
     * @return array{skill_id:string, score:float}|null
     */
    public function searchClosestSkill(string $text, float $threshold = 0.88){
        return   $this->vectorService?->searchClosestSkill(text: $text ,threshold:  $threshold);
    }
}