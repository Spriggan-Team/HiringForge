<?php

namespace App\Tests\Unit\Application\Candidate;

use App\Application\DTO\Candidate\RegisterCandidateCommand;
use App\Application\Usecases\Candidate\CandidateRegisterUsecase;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaStorageInterface;
use App\Domain\OTP\OTP;
use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\PasswordHasherInterface;
use PHPUnit\Framework\Attributes\Test;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CandidateRegisterUsecaseTest extends TestCase
{
    private AccountRepositoryInterface&MockObject $accountRepository;
    private CandidateRepositoryInterface&MockObject $candidateRepository;
    private PasswordHasherInterface&MockObject $hasher;
    private OTPRepositoryInterface&MockObject $otpRepository;
    private MediaStorageInterface&MockObject $storage;
    private MediaFactoryInterface&MockObject $mediaFactory;

    private CandidateRegisterUsecase $usecase;

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(AccountRepositoryInterface::class);
        $this->candidateRepository = $this->createMock(CandidateRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->otpRepository = $this->createMock(OTPRepositoryInterface::class);
        $this->storage = $this->createMock(MediaStorageInterface::class);
        $this->mediaFactory = $this->createMock(MediaFactoryInterface::class);

        $this->usecase = new CandidateRegisterUsecase(
            $this->accountRepository,
            $this->candidateRepository,
            $this->hasher,
            $this->otpRepository,
            $this->storage,
            $this->mediaFactory,
        );
    }

    #[Test]
    public function it_registers_a_candidate_successfully(): void
    {
        // Arrange
        $command = new RegisterCandidateCommand(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            password: 'Password123!',
            image: null,
            cv: null,
            address: null,
            searchRadius: 20,
            verificationCode: '123456',
        );

        $otp = $this->createMock(OTP::class);

        $this->accountRepository
            ->expects($this->once())
            ->method('exists')
            ->with(null, 'john@example.com')
            ->willReturn(false);

        $this->otpRepository
            ->expects($this->once())
            ->method('getLastVerificationTokenWithPurpose')
            ->with(
                'john@example.com',
                AccountFlowPurpose::SIGN_UP,
            )
            ->willReturn($otp);

        $otp
            ->expects($this->once())
            ->method('verify')
            ->with('123456', $this->hasher)
            ->willReturn(true);

        $this->hasher
            ->expects($this->once())
            ->method('hash')
            ->with('Password123!')
            ->willReturn('hashed-password');

        $this->candidateRepository
            ->expects($this->once())
            ->method('save');

        // Act
        $result = $this->usecase->execute($command);

        // Assert
        self::assertNotEmpty($result->userId);
        self::assertSame([], $result->failedUploads);
    }
}