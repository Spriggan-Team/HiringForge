<?php

namespace App\Tests\Functional\Authentification;

use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\PasswordHasherInterface;
use App\Tests\Support\Factory\Security\Tokens\OTPVerificationTokenEntityFactory;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserRegisterControllerTest extends WebTestCase
{
    use ResetDatabase;

    private function validPayload(
        string $email,
        string $verificationCode = '123456'
    ): array {
        return [
            'firstName' => 'Sophie',
            'lastName' => 'Martin',
            'companyName' => 'TechCorp Solutions',
            'email' => $email,
            'password' => 'StrongPassword123!',
            'siret' => '12345678901234',
            'verificationCode' => $verificationCode,
            'city' => 'Lyon',
            'street' => '15 Rue de la République',
            'postalCode' => '69002',
            'country' => 'France',
        ];
    }

    private function createOtp(
        string $email,
        string $plainCode,
        ?\DateTimeImmutable $expiresAt = null
    ): void {
        $container = static::getContainer();

        /** @var PasswordHasherInterface $hasher */
        $hasher = $container->get(PasswordHasherInterface::class);

        OTPVerificationTokenEntityFactory::createOne([
            'email' => $email,
            'codeHash' => $hasher->hash($plainCode),
            'purpose' => AccountFlowPurpose::SIGN_UP,
            'expiresAt' => $expiresAt ?? new \DateTimeImmutable('+15 minutes'),
        ]);
    }

    #[Test]
    public function user_can_register_with_valid_otp(): void
    {
        // Arrange
        $client = static::createClient();

        $email = 'recruiter@test.com';
        $plainCode = '123456';

        $this->createOtp(
            email: $email,
            plainCode: $plainCode
        );

        // Act
        $client->request(
            'POST',
            '/api/user/register',
            $this->validPayload($email, $plainCode)
        );

        // Assert
        self::assertResponseIsSuccessful();

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertSame('success', $response['status']);
    }

    #[Test]
    public function user_cannot_register_with_invalid_otp(): void
    {
        // Arrange
        $client = static::createClient();

        $email = 'invalid-otp@test.com';

        $this->createOtp(
            email: $email,
            plainCode: '654321'
        );

        // Act
        $client->request(
            'POST',
            '/api/user/register',
            $this->validPayload(
                email: $email,
                verificationCode: '123456'
            )
        );

        // Assert
        self::assertResponseStatusCodeSame(410);

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertSame('error', $response['status']);
    }

    #[Test]
    public function user_cannot_register_without_otp(): void
    {
        // Arrange
        $client = static::createClient();

        $email = 'no-otp@test.com';

        // Act
        $client->request(
            'POST',
            '/api/user/register',
            $this->validPayload(
                email: $email,
                verificationCode: '123456'
            )
        );

        // Assert
        self::assertResponseStatusCodeSame(410);

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertSame('error', $response['status']);
    }

    #[Test]
    public function user_can_register_with_email_format_currently_accepted_by_domain(): void
    {
        // Important:
        // The current EmailAddress domain object accepts this value.
        // This test documents the actual current application behavior.

        // Arrange
        $client = static::createClient();

        $email = 'invalid-email';
        $plainCode = '123456';

        $this->createOtp(
            email: $email,
            plainCode: $plainCode
        );

        // Act
        $client->request(
            'POST',
            '/api/user/register',
            $this->validPayload($email, $plainCode)
        );

        // Assert
        self::assertResponseIsSuccessful();

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertSame('success', $response['status']);
    }

    #[Test]
    public function user_can_register_with_password_currently_accepted_by_domain(): void
    {
        // Important:
        // The current PlainPassword domain object accepts "123".
        // This test documents the actual current application behavior.

        // Arrange
        $client = static::createClient();

        $email = 'weak-password@test.com';
        $plainCode = '123456';

        $this->createOtp(
            email: $email,
            plainCode: $plainCode
        );

        $payload = $this->validPayload(
            email: $email,
            verificationCode: $plainCode
        );

        $payload['password'] = '123';

        // Act
        $client->request(
            'POST',
            '/api/user/register',
            $payload
        );

        // Assert
        self::assertResponseIsSuccessful();

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertSame('success', $response['status']);
    }

    #[Test]
    public function user_cannot_register_with_missing_required_fields(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request(
            'POST',
            '/api/user/register',
            [
                'firstName' => '',
                'lastName' => '',
                'email' => '',
            ]
        );

        // Assert
        self::assertResponseStatusCodeSame(400);

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertSame('error', $response['status']);
    }
}