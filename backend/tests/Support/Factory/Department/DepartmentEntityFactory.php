<?php

namespace App\Tests\Support\Factory\Department;

use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;
use App\Tests\Support\Factory\Company\CompanyEntityFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<DepartmentEntity>
 */
final class DepartmentEntityFactory extends PersistentObjectFactory
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
        return DepartmentEntity::class;
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
            'isActive' => self::faker()->boolean(),
            'label' => self::faker()->text(120),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(DepartmentEntity $departmentEntity): void {})
        ;
    }
}
