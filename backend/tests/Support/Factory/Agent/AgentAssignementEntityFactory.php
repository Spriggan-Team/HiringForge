<?php

namespace App\Tests\Support\Factory\Agent;

use App\Infrastructure\Persistence\Doctrine\ORM\Agent\AgentAssignementEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\EntityType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AgentAssignementEntity>
 */
final class AgentAssignementEntityFactory extends PersistentObjectFactory
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
        return AgentAssignementEntity::class;
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
            'agent' => AgentEntityFactory::new(),
            'assignedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'assignedBy' => self::faker()->uuid(),
            'entityType' => self::faker()->randomElement(EntityType::cases()),
            'expiredAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(AgentAssignementEntity $agentAssignementEntity): void {})
        ;
    }
}
