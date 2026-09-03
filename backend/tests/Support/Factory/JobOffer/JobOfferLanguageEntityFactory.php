<?php

namespace App\Tests\Support\Factory\JobOffer;

use App\Domain\Shared\LanguageLevel;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferLanguageEntity;
use App\Tests\Support\Factory\Global\Language\LanguageEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<JobOfferLanguageEntity>
 */
final class JobOfferLanguageEntityFactory extends PersistentObjectFactory
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
        return JobOfferLanguageEntity::class;
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
            'jobOffer' => JobOfferEntityFactory::new(),
            'language' => LanguageEntityFactory::new(),
            'level' => self::faker()->randomElement(LanguageLevel::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(JobOfferLanguageEntity $jobOfferLanguageEntity): void {})
        ;
    }
}
