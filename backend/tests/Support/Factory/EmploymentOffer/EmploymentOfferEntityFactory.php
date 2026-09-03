<?php

namespace App\Tests\Support\Factory\EmploymentOffer;

use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\EmploymentOfferEntity;
use App\Tests\Support\Factory\Candidate\ApplicationEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<EmploymentOfferEntity>
 */
final class EmploymentOfferEntityFactory extends PersistentObjectFactory
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
        return EmploymentOfferEntity::class;
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
            'application' => ApplicationEntityFactory::new(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'expiredAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'scheduledEndDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'status' => self::faker()->randomElement(EmploymentOfferStatus::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(EmploymentOfferEntity $employmentOfferEntity): void {})
        ;
    }
}
