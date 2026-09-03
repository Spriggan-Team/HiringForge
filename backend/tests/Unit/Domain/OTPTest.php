<?php

namespace App\Tests\Unit\Domain\OTP;

use App\Domain\OTP\Exceptions\OTPException;
use App\Domain\OTP\OTP;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\PasswordHasherInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OTPTest extends TestCase
{
    #[Test]
    public function it_verifies_a_valid_otp_code(): void
    {
        // Arrange
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $hasher
            ->expects($this->once())
            ->method('verify')
            ->with('123456', 'hashed-code')
            ->willReturn(true);

        $otp = OTP::hydrate(
            hashCode: 'hashed-code',
            expiresAt: new \DateTimeImmutable('+10 minutes'),
            purpose: AccountFlowPurpose::SIGN_UP
        );

        // Act
        $result = $otp->verify('123456', $hasher);

        // Assert
        self::assertTrue($result);
        self::assertSame(1, $otp->getAttempts());
    }

    #[Test]
    public function it_throws_an_exception_when_otp_is_expired(): void
    {
        // Arrange
        $hasher = $this->createMock(PasswordHasherInterface::class);

        $otp = OTP::hydrate(
            hashCode: 'hashed-code',
            expiresAt: new \DateTimeImmutable('-1 minute'),
            purpose: AccountFlowPurpose::SIGN_UP
        );

        // Assert
        $this->expectException(OTPException::class);

        // Act
        $otp->verify('123456', $hasher);
    }

    #[Test]
    public function it_throws_an_exception_when_maximum_attempts_are_reached(): void
    {
        // Arrange
        $hasher = $this->createMock(PasswordHasherInterface::class);

        $otp = OTP::hydrate(
            hashCode: 'hashed-code',
            expiresAt: new \DateTimeImmutable('+10 minutes'),
            purpose: AccountFlowPurpose::SIGN_UP,
            attempts: 5
        );

        // Assert
        $this->expectException(OTPException::class);

        // Act
        $otp->verify('123456', $hasher);
    }
}