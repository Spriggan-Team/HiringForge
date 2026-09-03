<?php

namespace App\Tests\Unit\Application\User;

use App\Application\DTO\User\RegisterUserCommand;

use App\Application\Usecases\Account\AccountRegister;
use App\Application\Usecases\User\UserRegisterUseCase;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Department\DepartmentInitializerService;

use App\Domain\Exception\CompanyAlreadyRegistered;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\ResourceCreationRejected;
use App\Domain\Exception\ResourceNotFoundException;

use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaStorageInterface;
use App\Domain\File\StaticMedia;
use App\Domain\OTP\Exceptions\OTPException;

use App\Domain\OTP\OTP;
use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\Address;
use App\Domain\Shared\KnownIdentity;

use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\TransactionManagerInterface;
use App\Domain\User\UserRepositoryInterface;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UserRegisterUseCaseTest extends TestCase
{
    private MediaFactoryInterface&MockObject $mediaFactory;
    private MediaStorageInterface&MockObject $storage;
    private UserRepositoryInterface&MockObject $userRepository;
    private CompanyRepositoryInterface&MockObject $companyRepository;
    private PasswordHasherInterface&MockObject $hasher;
    private OTPRepositoryInterface&MockObject $otpRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private DepartmentInitializerService&MockObject $departmentInitializer;

    private UserRegisterUseCase $useCase;

    protected function setUp(): void
    {
        $this->mediaFactory = $this->createMock(MediaFactoryInterface::class);
        $this->storage = $this->createMock(MediaStorageInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->companyRepository = $this->createMock(CompanyRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->otpRepository = $this->createMock(OTPRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->departmentInitializer = $this->createMock(DepartmentInitializerService::class);

        $this->useCase = new UserRegisterUseCase(
            $this->mediaFactory,
            $this->storage,
            $this->userRepository,
            $this->companyRepository,
            $this->hasher,
            $this->otpRepository,
            $this->transactionManager,
            $this->departmentInitializer
        );
    }

    #[Test]
    public function it_registers_a_user_successfully_without_files(): void
    {
        $command = $this->createValidCommand();

        $this->userRepository
            ->expects($this->once())
            ->method('assertExist')
            ->with(
                self::isNull(),
                self::equalTo($command->email)
            )
            ->willThrowException(
                new ResourceNotFoundException()
            );

        $otp = OTP::hydrate(
            hashCode: 'hashed_code',
            expiresAt: new \DateTimeImmutable('+10 minutes'),
            purpose: AccountFlowPurpose::SIGN_UP
        );

        $this->otpRepository
            ->expects($this->once())
            ->method('getLastVerificationTokenWithPurpose')
            ->with(
                $command->email,
                AccountFlowPurpose::SIGN_UP
            )
            ->willReturn($otp);

        $this->hasher
            ->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->hasher
            ->expects($this->once())
            ->method('hash')
            ->willReturn('hashed_password');

        $this->companyRepository
            ->expects($this->once())
            ->method('exists')
            ->with($command->companyName)
            ->willReturn(false);

        $this->transactionManager
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                static fn (callable $callback) => $callback()
            );

        $this->companyRepository
            ->expects($this->once())
            ->method('save');

        $this->userRepository
            ->expects($this->once())
            ->method('save');

        $this->departmentInitializer
            ->expects($this->once())
            ->method('initForCompany');

        $result = $this->useCase->execute($command);

        self::assertInstanceOf(AccountRegister::class, $result);
        self::assertEmpty($result->filesFailedGeneric);
        self::assertEmpty($result->successfulUploads);
    }


    #[Test]
    public function it_throws_an_exception_when_email_is_already_registered(): void
    {
        $command = $this->createValidCommand();

        $this->userRepository
            ->expects($this->once())
            ->method('assertExist')
            ->with(
                self::isNull(),
                self::equalTo($command->email)
            )
            ->willReturn(
                $this->createMock(KnownIdentity::class)
            );

        $this->expectException(EmailAlreadyRegistered::class);

        $this->useCase->execute($command);
    }


    #[Test]
    public function it_throws_an_exception_when_otp_is_invalid(): void
    {
        $command = $this->createValidCommand();

        $this->userRepository
            ->expects($this->once())
            ->method('assertExist')
            ->with(
                self::isNull(),
                self::equalTo($command->email)
            )
            ->willThrowException(
                new ResourceNotFoundException()
            );

        $otp = OTP::hydrate(
            hashCode: 'hashed_code',
            expiresAt: new \DateTimeImmutable('+10 minutes'),
            purpose: AccountFlowPurpose::SIGN_UP
        );

        $this->otpRepository
            ->expects($this->once())
            ->method('getLastVerificationTokenWithPurpose')
            ->willReturn($otp);

        $this->hasher
            ->expects($this->once())
            ->method('verify')
            ->willReturn(false);

        $this->expectException(OTPException::class);

        $this->useCase->execute($command);
    }


    #[Test]
    public function it_throws_an_exception_when_company_is_already_registered(): void
    {
        $command = $this->createValidCommand();

        $this->userRepository
            ->expects($this->once())
            ->method('assertExist')
            ->with(
                self::isNull(),
                self::equalTo($command->email)
            )
            ->willThrowException(
                new ResourceNotFoundException()
            );

        $otp = OTP::hydrate(
            hashCode: 'hashed_code',
            expiresAt: new \DateTimeImmutable('+10 minutes'),
            purpose: AccountFlowPurpose::SIGN_UP
        );

        $this->otpRepository
            ->method('getLastVerificationTokenWithPurpose')
            ->willReturn($otp);

        $this->hasher
            ->method('verify')
            ->willReturn(true);

        $this->companyRepository
            ->expects($this->once())
            ->method('exists')
            ->with($command->companyName)
            ->willReturn(true);

        $this->expectException(CompanyAlreadyRegistered::class);

        $this->useCase->execute($command);
    }


    #[Test]
    public function it_removes_uploaded_files_when_database_transaction_fails(): void
    {
        // Arrange
        $command = $this->createValidCommand();

        $file = $this->createMock(UploadedFile::class);
        $command->logo = $file;

        $this->userRepository
            ->method('assertExist')
            ->willThrowException(new ResourceNotFoundException());

        $otp = OTP::hydrate(
            hashCode: 'hashed_code',
            expiresAt: new \DateTimeImmutable('+10 minutes'),
            purpose: AccountFlowPurpose::SIGN_UP
        );

        $this->otpRepository
            ->method('getLastVerificationTokenWithPurpose')
            ->willReturn($otp);

        $this->hasher
            ->method('verify')
            ->willReturn(true);

        $this->companyRepository
            ->method('exists')
            ->willReturn(false);

        $staticMedia = new StaticMedia(
            name: 'logo.png',
            originalName: 'logo.png',
            size: 1024,
            mime: 'image/png'
        );

        $this->mediaFactory
            ->expects($this->once())
            ->method('createStaticMedia')
            ->willReturn($staticMedia);

        $this->storage
            ->expects($this->once())
            ->method('store')
            ->willReturnCallback(
                static function (...$args): void {
                    $successCallback = $args[3];

                    $successCallback(
                        (object) [
                            'storedName' => 'logo.png'
                        ]
                    );
                }
            );

        $this->transactionManager
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(
                new ResourceCreationRejected('DB Error')
            );

        $this->storage
            ->expects($this->once())
            ->method('remove');

        $this->expectException(ResourceCreationRejected::class);

        // Act
        $this->useCase->execute($command);
    }

    private function createValidCommand(): RegisterUserCommand
    {
        return new RegisterUserCommand(
            lastName: 'Doe',
            firstName: 'John',
            companyName: 'Acme Corp',
            email: 'john.recruiter@acme.com',
            password: 'Password123!',
            siret: '12345678901234',
            verificationCode: '123456',
            address: Address::create(
                city: 'Paris',
                street: '10 Rue de la Paix',
                postalCode: '75002',
                country: 'France'
            )
        );
    }
}