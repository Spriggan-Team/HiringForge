<?php


namespace App\Domain\Shared\Skill;



final class SkillScoreCalculator
{
    /**
     * @param RequiredSkill[] $requiredSkills
     * @param SkillMatch[]    $matches
     */
    public function calculate(
        array $requiredSkills,
        array $matches,
    ): float {
        if ($requiredSkills === []) {
            return 0.0;
        }

        $matchesBySkillId = [];

        foreach ($matches as $match) {
            $matchesBySkillId[$match->skillId] = $match;
        }

        $weightedScore = 0.0;
        $totalWeight = 0.0;

        foreach ($requiredSkills as $requiredSkill) {
            $weight = max(0.0, $requiredSkill->weight);

            $totalWeight += $weight;

            $match = $matchesBySkillId[$requiredSkill->skillId] ?? null;

            if ($match === null) {
                continue;
            }

            $weightedScore +=
                $weight * $match->confidence();
        }

        if ($totalWeight <= 0.0) {
            return 0.0;
        }

        return round(
            ($weightedScore / $totalWeight) * 100,
            2,
        );
    }
}