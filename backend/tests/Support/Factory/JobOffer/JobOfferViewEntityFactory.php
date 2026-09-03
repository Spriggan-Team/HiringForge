<?php

namespace App\Tests\Support\Factory\JobOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
use App\Tests\Support\Factory\Candidate\CandidateEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<JobOfferViewEntity>
 */
final class JobOfferViewEntityFactory extends PersistentObjectFactory
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
        return JobOfferViewEntity::class;
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
            'jobOffer' => JobOfferEntityFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(JobOfferViewEntity $jobOfferViewEntity): void {})
        ;
    }
}
