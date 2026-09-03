<?php

namespace App\Tests\Support\Factory\Interview;

use App\Domain\Interviews\InterviewStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<InterviewEntity>
 */
final class InterviewEntityFactory extends PersistentObjectFactory
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
        return InterviewEntity::class;
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
            'candidateApproval' => self::faker()->boolean(),
            'startDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'status' => self::faker()->randomElement(InterviewStatus::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(InterviewEntity $interviewEntity): void {})
        ;
    }
}
