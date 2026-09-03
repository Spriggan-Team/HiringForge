<?php

namespace App\Tests\Support\Factory\JobOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobCategoryEntity;
use App\Tests\Support\Factory\Global\Category\CategoryEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<JobCategoryEntity>
 */
final class JobCategoryEntityFactory extends PersistentObjectFactory
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
        return JobCategoryEntity::class;
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
            'category' => CategoryEntityFactory::new(),
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
            // ->afterInstantiate(function(JobCategoryEntity $jobCategoryEntity): void {})
        ;
    }
}
