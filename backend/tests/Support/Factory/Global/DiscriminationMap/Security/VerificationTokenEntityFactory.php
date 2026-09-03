<?php

namespace App\Tests\Support\Factory\Global\DiscriminationMap\Security;

use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Security\VerificationTokenEntity;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<VerificationTokenEntity>
 */
final class VerificationTokenEntityFactory extends PersistentObjectFactory
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
        return VerificationTokenEntity::class;
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
            'code_hash' => self::faker()->text(255),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'expiresAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'purpose' => self::faker()->randomElement(AccountFlowPurpose::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
