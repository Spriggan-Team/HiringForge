<?php

namespace App\Tests\Support\Factory\Global\Notification;

use App\Domain\Notification\NotificationType;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification\NotificationEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<NotificationEntity>
 */
final class NotificationEntityFactory extends PersistentObjectFactory
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
        return NotificationEntity::class;
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
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'data' => [],
            'isRead' => self::faker()->boolean(),
            'type' => self::faker()->randomElement(NotificationType::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(NotificationEntity $notificationEntity): void {})
        ;
    }
}
