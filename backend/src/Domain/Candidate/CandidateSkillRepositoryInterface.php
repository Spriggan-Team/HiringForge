<?php

namespace App\Domain\Candidate;

use App\Domain\Shared\Skill\BasicSkillModel;
use App\Domain\Shared\Skill\SkillResolution;

interface CandidateSkillRepositoryInterface
{
    public function link(
        string $candidateId,
        string $skillId,
        ?string $candidateResumeId = null
    ): void;


    public function unlink(
        string $candidateId,
        string $skillId
    ): void;

    public function resolveSkill(string $candidateId, string $text, ?string $locale = null): ?SkillResolution;

    public function hasSkill(string $candidateId, string $skillId): bool;

    /**
     * Get all skills related to a candidate.
     *
     * @return array<int, BasicSkillModel>
     */
    public function getCandidateSkills(string $candidateId): array;
}