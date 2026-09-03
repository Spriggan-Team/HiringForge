<?php

namespace App\Tests\Support\Factory\Global\DiscriminationMap\Account;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AdminSupervisionEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AdminSupervisionEntity>
 */
final class AdminSupervisionEntityFactory extends PersistentObjectFactory
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
        return AdminSupervisionEntity::class;
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
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(AdminSupervisionEntity $adminSupervisionEntity): void {})
        ;
    }
}
