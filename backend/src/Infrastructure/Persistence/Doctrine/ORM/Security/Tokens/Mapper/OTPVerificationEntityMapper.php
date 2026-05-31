<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens\Mapper;

use App\Domain\OTP\OTP as DomainEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens\OTPVerificationTokenEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;



final class OTPVerificationEntityMapper
{
    public static function toDomainEntity(OTPVerificationTokenEntity $entity): DomainEntity
    {
        return DomainEntity::hydrate(
            id: $entity->getId(),
            attempts: $entity->getAttempts(),
            hashCode: $entity->getCodeHash(),
            expiresAt: $entity->getExpiresAt(),
            purpose: $entity->getPurpose(),
            email: $entity->getEmail()
        );
    }

    public static function toDoctrineEntity(
        DomainEntity $otp,
        ?AccountEntity $account = null
    ): OTPVerificationTokenEntity {

        return new OTPVerificationTokenEntity(
            account: $account,
            email: $otp->getEmail()
        );
    }

    public static function copy(
        DomainEntity $otp,
        OTPVerificationTokenEntity $entity
    ): void {
        $entity
            ->setEmail($otp->getEmail())
            ->setCodeHash($otp->getHashCode())
            ->setPurpose($otp->getPurpose())
            ->setExpiresAt($otp->getExpiresAt())
            ->setAttempts($otp->getAttempts());
    }
}