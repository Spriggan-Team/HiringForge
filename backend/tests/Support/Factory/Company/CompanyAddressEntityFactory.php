<?php

namespace App\Tests\Support\Factory\Company;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyAddressEntity;
use App\Tests\Support\Factory\Global\Address\AddressEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CompanyAddressEntity>
 */
final class CompanyAddressEntityFactory extends PersistentObjectFactory
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
        return CompanyAddressEntity::class;
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
            'address' => AddressEntityFactory::new(),
            'company' => CompanyEntityFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(CompanyAddressEntity $companyAddressEntity): void {})
        ;
    }
}
