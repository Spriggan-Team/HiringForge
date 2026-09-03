<?php

namespace App\Tests\Support\Factory\Global\DiscriminationMap\Account;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AccountEntity>
 */
final class AccountEntityFactory extends PersistentObjectFactory
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
        return AccountEntity::class;
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
            'email' => self::faker()->text(255),
            'firstName' => self::faker()->text(150),
            'lastName' => self::faker()->text(150),
            'password' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(AccountEntity $accountEntity): void {})
        ;
    }
}
