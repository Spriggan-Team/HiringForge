<?php

namespace App\Tests\Support\Factory\JobOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferImageEntity;
use App\Tests\Support\Factory\Global\File\FileEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<JobOfferImageEntity>
 */
final class JobOfferImageEntityFactory extends PersistentObjectFactory
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
        return JobOfferImageEntity::class;
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
            'file' => FileEntityFactory::new(),
            'isMain' => self::faker()->boolean(),
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
            // ->afterInstantiate(function(JobOfferImageEntity $jobOfferImageEntity): void {})
        ;
    }
}
