<?php

namespace App\Tests\Support\Factory\JobOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferSkillsEntity;
use App\Tests\Support\Factory\Global\Skill\SkillEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<JobOfferSkillsEntity>
 */
final class JobOfferSkillsEntityFactory extends PersistentObjectFactory
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
        return JobOfferSkillsEntity::class;
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
            'isRequired' => self::faker()->boolean(),
            'jobOffer' => JobOfferEntityFactory::new(),
            'skill' => SkillEntityFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(JobOfferSkillsEntity $jobOfferSkillsEntity): void {})
        ;
    }
}
