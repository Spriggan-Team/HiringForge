<?php

namespace App\Tests\Support\Factory\Security\Tokens;

use App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens\BlacklistedTokenEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<BlacklistedTokenEntity>
 */
final class BlacklistedTokenEntityFactory extends PersistentObjectFactory
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
        return BlacklistedTokenEntity::class;
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
            'expiresAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'jti' => self::faker()->text(36),
            'reason' => self::faker()->text(50),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(BlacklistedTokenEntity $blacklistedTokenEntity): void {})
        ;
    }
}
