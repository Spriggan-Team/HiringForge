<?php

namespace App\Tests\Support\Factory\JobOffer;

use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<JobOfferEntity>
 */
final class JobOfferEntityFactory extends PersistentObjectFactory
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
        return JobOfferEntity::class;
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
            'content' => [],
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'publicationStatus' => self::faker()->randomElement(JobPublicationStatus::cases()),
            'title' => self::faker()->text(255),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'visibilityStatus' => self::faker()->randomElement(JobOfferVisibilityStatus::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(JobOfferEntity $jobOfferEntity): void {})
        ;
    }
}
