<?php

namespace App\Tests\Support\Factory\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Tests\Support\Factory\Global\File\FileEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CandidateResumeEntity>
 */
final class CandidateResumeEntityFactory extends PersistentObjectFactory
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
        return CandidateResumeEntity::class;
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
            'file' => FileEntityFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(CandidateResumeEntity $candidateResumeEntity): void {})
        ;
    }
}
