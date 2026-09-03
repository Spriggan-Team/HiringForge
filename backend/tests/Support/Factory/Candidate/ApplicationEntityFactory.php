<?php

namespace App\Tests\Support\Factory\Candidate;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Tests\Support\Factory\Company\CompanyEntityFactory;
use App\Tests\Support\Factory\JobOffer\JobOfferEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ApplicationEntity>
 */
final class ApplicationEntityFactory extends PersistentObjectFactory
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
        return ApplicationEntity::class;
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
            'candidate' => CandidateEntityFactory::new(),
            'company' => CompanyEntityFactory::new(),
            'jobOffer' => JobOfferEntityFactory::new(),
            'matchScore' => self::faker()->randomFloat(),
            'status' => self::faker()->randomElement(JobApplicationStatus::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ApplicationEntity $applicationEntity): void {})
        ;
    }
}
