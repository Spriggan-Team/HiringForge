<?php

namespace App\Tests\Functional\Authentification;

use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\PasswordHasherInterface;
use App\Tests\Support\Factory\Security\Tokens\OTPVerificationTokenEntityFactory;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;



class CandidateRegisterControllerTest extends WebTestCase
{
    use ResetDatabase;

    public function test_candidate_can_register_with_valid_otp(): void
    {
        $client = static::createClient();
        $hasher = static::getContainer()->get(PasswordHasherInterface::class);

        $email = 'candidate.success@test.com';
        $code = '123456';

        // BDD OTP
        OTPVerificationTokenEntityFactory::createOne([
            'email' => $email,
            'codeHash' => $hasher->hash($code),
            'purpose' => AccountFlowPurpose::SIGN_UP,
            'expiresAt' => new \DateTimeImmutable('+15 minutes'),
            'attempts' => 0,
        ]);

        // Register Request
        $client->request(
            'POST',
            '/api/candidate/register',
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => $email,
                'password' => 'Password123!',
                'searchRadius' => 10,
                'verificationCode' => $code,
            ]
        );

        self::assertResponseIsSuccessful();
    }


    public function test_candidate_cannot_register_if_otp_is_invalid(): void
    {
        $client = static::createClient();
        $hasher = static::getContainer()->get(PasswordHasherInterface::class);

        $email = 'candidate.invalid.otp@test.com';

        OTPVerificationTokenEntityFactory::createOne([
            'email' => $email,
            'codeHash' => $hasher->hash('123456'),
            'purpose' => AccountFlowPurpose::SIGN_UP,
            'expiresAt' => new \DateTimeImmutable('+15 minutes'),
        ]);

        $client->request(
            'POST',
            '/api/candidate/register',
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => $email,
                'password' => 'Password123!',
                'searchRadius' => 10,
                'verificationCode' => '000000', // Mauvais code
            ]
        );

        self::assertResponseStatusCodeSame(410);
    }


    public function test_candidate_cannot_register_if_otp_is_expired(): void
    {
        $client = static::createClient();
        $hasher = static::getContainer()->get(PasswordHasherInterface::class);

        $email = 'candidate.expired@test.com';

        OTPVerificationTokenEntityFactory::createOne([
            'email' => $email,
            'codeHash' => $hasher->hash('123456'),
            'purpose' => AccountFlowPurpose::SIGN_UP,
            'expiresAt' => new \DateTimeImmutable('-5 minutes'), // Expiré
        ]);

        $client->request(
            'POST',
            '/api/candidate/register',
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => $email,
                'password' => 'Password123!',
                'searchRadius' => 10,
                'verificationCode' => '123456',
            ]
        );

        self::assertResponseStatusCodeSame(410);
    }
}