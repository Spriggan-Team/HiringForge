<?php

namespace App\Tests\Support\Factory\Global\Skill;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;
use App\Tests\Support\Factory\Global\Language\LanguageEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<SkillTranslationEntity>
 */
final class SkillTranslationEntityFactory extends PersistentObjectFactory
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
        return SkillTranslationEntity::class;
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
            'language' => LanguageEntityFactory::new(),
            'name' => self::faker()->text(120),
            'skill' => SkillEntityFactory::new(),
            'slug' => self::faker()->text(120),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(SkillTranslationEntity $skillTranslationEntity): void {})
        ;
    }
}
