<?php

namespace App\Tests\Support\Factory\Security\Tokens;

use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens\OTPVerificationTokenEntity;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<OTPVerificationTokenEntity>
 */
final class OTPVerificationTokenEntityFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return OTPVerificationTokenEntity::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->email(),
            'codeHash' => self::faker()->sha256(), // Mappe directement sur setCodeHash()
            'purpose' => AccountFlowPurpose::SIGN_UP,
            'expiresAt' => new \DateTimeImmutable('+15 minutes'),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): OTPVerificationTokenEntity {
            //-- Manual
            $entity = new OTPVerificationTokenEntity(
                account: $attributes['account'] ?? null,
                email: $attributes['email'] ?? null
            );

            //  Hydratation explicite via tes setters
            if (isset($attributes['codeHash'])) {
                $entity->setCodeHash($attributes['codeHash']);
            }


            if (isset($attributes['purpose'])) {
                $entity->setPurpose($attributes['purpose']);
            }

            if (isset($attributes['expiresAt'])) {
                $entity->setExpiresAt($attributes['expiresAt']);
            }

            if (isset($attributes['attempts'])) {
                $entity->setAttempts($attributes['attempts']);
            }

            return $entity;
        });
    }

    public function expired(): static
    {
        return $this->with([
            'expiresAt' => new \DateTimeImmutable('-10 minutes'),
        ]);
    }
}