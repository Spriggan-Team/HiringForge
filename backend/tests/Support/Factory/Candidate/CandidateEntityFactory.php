<?php

namespace App\Tests\Support\Factory\Candidate;

use App\Domain\Candidate\CandidateStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CandidateEntity>
 */
final class CandidateEntityFactory extends PersistentObjectFactory
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
        return CandidateEntity::class;
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
            'searchRadius' => self::faker()->randomNumber(),
            'status' => self::faker()->randomElement(CandidateStatus::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(CandidateEntity $candidateEntity): void {})
        ;
    }
}
