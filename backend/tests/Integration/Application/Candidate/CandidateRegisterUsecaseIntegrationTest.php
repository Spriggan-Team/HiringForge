<?php

namespace App\Tests\Integration\Application\Candidate;


use App\Application\DTO\Candidate\RegisterCandidateCommand;
use App\Application\Usecases\Candidate\CandidateRegisterUsecase;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\PasswordHasherInterface;

use App\Tests\Support\Factory\Security\Tokens\OTPVerificationTokenEntityFactory;
use PHPUnit\Framework\Attributes\Test;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;


class CandidateRegisterUsecaseIntegrationTest extends KernelTestCase
{
    use ResetDatabase;

    private CandidateRegisterUsecase $usecase;
    private CandidateRepositoryInterface $candidateRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->usecase = $container->get(CandidateRegisterUsecase::class);
        $this->candidateRepository = $container->get(CandidateRepositoryInterface::class);
    }

    #[Test]
    public function it_persists_candidate_in_database_with_valid_otp(): void
    {
        $email = 'candidate_integration@test.com';
        $code = '654321';

        /** @var PasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(PasswordHasherInterface::class);

        // Create & persist OTP directly into bdd
        OTPVerificationTokenEntityFactory::createOne([
            'email' => $email,
            'codeHash' => $hasher->hash($code), // <-- camelCase
            'purpose' => AccountFlowPurpose::SIGN_UP,
            'expiresAt' => new \DateTimeImmutable('+10 minutes'),
        ]);

        $command = new RegisterCandidateCommand(
            firstName: 'John',
            lastName: 'Doe',
            email: $email,
            password: 'Password123!',
            image: null,
            cv: null,
            address: null,
            searchRadius: 25,
            verificationCode: $code
        );

        $result = $this->usecase->execute($command);

        self::assertNotEmpty($result->userId);
        
        $candidate = $this->candidateRepository->exists($result->userId);
        self::assertNotNull($candidate);
    }
}