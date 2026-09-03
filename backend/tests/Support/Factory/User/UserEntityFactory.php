<?php

namespace App\Tests\Support\Factory\User;

use App\Domain\User\UserRole;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Tests\Support\Factory\Company\CompanyEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<UserEntity>
 */
final class UserEntityFactory extends PersistentObjectFactory
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
        return UserEntity::class;
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
            'company' => CompanyEntityFactory::new(),
            'email' => self::faker()->text(255),
            'firstName' => self::faker()->text(150),
            'lastName' => self::faker()->text(150),
            'password' => self::faker()->text(255),
            'userRole' => self::faker()->randomElement(UserRole::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(UserEntity $userEntity): void {})
        ;
    }
}
