<?php

namespace App\Tests\Support\Factory\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateSkillsEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CandidateSkillsEntity>
 */
final class CandidateSkillsEntityFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return CandidateSkillsEntity::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'candidate' => CandidateEntityFactory::new(),
            'source' => self::faker()->text(20),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(CandidateSkillsEntity $candidateSkillsEntity): void {})
        ;
    }
}
