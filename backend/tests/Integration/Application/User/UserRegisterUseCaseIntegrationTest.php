<?php

namespace App\Tests\Integration\Application\User;

use App\Application\DTO\User\RegisterUserCommand;
use App\Application\Usecases\Account\AccountRegister;
use App\Application\Usecases\User\UserRegisterUseCase;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\OTP\Exceptions\OTPException;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\Address;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Tests\Support\Factory\Security\Tokens\OTPVerificationTokenEntityFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserRegisterUseCaseIntegrationTest extends KernelTestCase
{
    use ResetDatabase;

    private UserRegisterUseCase $useCase;
    private UserRepositoryInterface $userRepository;
    private CompanyRepositoryInterface $companyRepository;
    private PasswordHasherInterface $hasher;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->useCase = $container->get(UserRegisterUseCase::class);
        $this->userRepository = $container->get(UserRepositoryInterface::class);
        $this->companyRepository = $container->get(CompanyRepositoryInterface::class);
        $this->hasher = $container->get(PasswordHasherInterface::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function test_persists_user_and_company_in_database(): void
    {
        // Create account otp& dto
        $email = 'recruiter.test@company.com';
        $code = '123456';

        OTPVerificationTokenEntityFactory::createOne([
            'email' => $email,
            'codeHash' => $this->hasher->hash($code),
            'purpose' => AccountFlowPurpose::SIGN_UP,
        ]);

        $command = new RegisterUserCommand(
            lastName: 'Martin',
            firstName: 'Sophie',
            companyName: 'TechCorp Solutions',
            email: $email,
            password: 'StrongPassword123!',
            siret: '12345678901234',
            verificationCode: $code,
            address: Address::create('Lyon', '15 Rue de la République', '69002', 'France')
        );

        // execute usecase
        $result = $this->useCase->execute($command);

        // Vider le Cache Unit of Work pour forcer la lecture réelle SQL
        $this->entityManager->clear();

        // What is awaited
        $this->assertInstanceOf(AccountRegister::class, $result);

        $savedUser = $this->userRepository->findByEmail($email);
        $this->assertNotNull($savedUser, 'L\'utilisateur doit être trouvé en BDD');
        $this->assertEquals('Martin', $savedUser->lastName());

        $this->assertTrue(
            $this->companyRepository->exists('TechCorp Solutions'),
            'L\'entreprise doit exister en BDD'
        );
    }
    

    public function test_fails_and_rolls_back_if_otp_is_invalid(): void
    {
        // OTP with hash code
        $email = 'wrong.otp@company.com';

        OTPVerificationTokenEntityFactory::createOne([
            'email' => $email,
            'codeHash' => $this->hasher->hash('654321'),
            'purpose' => AccountFlowPurpose::SIGN_UP,
        ]);

        $command = new RegisterUserCommand(
            lastName: 'Dupont',
            firstName: 'Jean',
            companyName: 'FailCorp',
            email: $email,
            password: 'StrongPassword123!',
            siret: '12345678901234',
            verificationCode: '123456',
            address: Address::create('Paris', '1 Rue de la Paix', '75001', 'France')
        );

        // TEHN & WEHN
        $this->expectException(OTPException::class);

        try {
            $this->useCase->execute($command);
        } finally {
            $this->entityManager->clear();

            $userNotFound = false;
            try {
                $this->userRepository->findByEmail($email);
            }
            catch (ResourceNotFoundException) {
                $userNotFound = true;
            }

            $this->assertTrue($userNotFound, 'L\'utilisateur ne doit pas exister en BDD après le rollback');
            $this->assertFalse($this->companyRepository->exists('FailCorp'));
        }
    }
}