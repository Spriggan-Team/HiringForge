<?php

namespace App\Domain\OTP;

use App\Domain\OTP\Exception\OTPException;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\PasswordHasherInterface;

use DateTimeImmutable;
use DateInterval;
use DomainException;

/**
 * Represents a One-Time Password (OTP) for secure actions.
 * Encapsulates hash, TTL, attempts, and domain validation.
 */
class OTP
{
    public ?DateInterval $remainingValidityDuration = null;

    private function __construct(
        public int $attempts,
        public string $hashCode,
        public DateTimeImmutable $expiresAt,
        public AccountFlowPurpose $purpose,
        private ?string $email = null,
        public ?string $plainCode = null,
        public ?string $id = null,
    ) {}

    /**
     * Create a new OTP instance.
     *
     * @param PasswordHasherInterface $hasher Used to hash the OTP securely
     * @param AccountFlowPurpose $purpose Purpose of the OTP (password reset, email change, etc.)
     * @param int $attempts Number of attempts already used (default 0)
     * @param string|null $plainCode Optional pre-generated OTP code
     */
    public static function create(
        PasswordHasherInterface $hasher,
        AccountFlowPurpose $purpose = AccountFlowPurpose::PASSWORD_RESET,
        ?string $hashCode = null,
        int $attempts = 0,
        ?string $plainCode = null,
        ?string $id = null,
    ): self {
        $plainCode = $plainCode ?? self::generateCode();

        if (!self::isValid($plainCode)) {
            throw new DomainException("Generated OTP code is invalid");
        }

        $hashCode = $hashCode ?? $hasher->hash($plainCode);

        $expiresAt = new DateTimeImmutable();
        switch ($purpose) {
            case AccountFlowPurpose::PASSWORD_RESET:
                $expiresAt = $expiresAt->add(new DateInterval("PT5M"));
                break;
            default:
                $expiresAt = $expiresAt->add(new DateInterval("PT15M"));
        }

        return new self(
            attempts: $attempts,
            hashCode: $hashCode,
            expiresAt: $expiresAt,
            purpose: $purpose,
            email: null,
            plainCode: $plainCode,
            id: $id
        );
    }

    /**
     * WARNING: This should only be used when input data are guaranteed to be safe
     * because this function bypasses domain validation constraints.
     */
    public static function hydrate(
        string $hashCode,
        DateTimeImmutable $expiresAt,
        AccountFlowPurpose $purpose,
        int $attempts = 0,
        ?string $email = null,
        ?string $id = null
    ): self {
        return new self(
            attempts: $attempts,
            hashCode: $hashCode,
            expiresAt: $expiresAt,
            purpose: $purpose,
            email: $email,
            plainCode: null,
            id: $id
        );
    }

    /**
     * Verify a plain code against the stored hashed OTP.
     *
     * @param string $plainCode Code provided by the user
     * @param PasswordHasherInterface $hasher Hasher used to verify the code
     * @return bool True if valid
     * @throws OTPException  If expired or attempts exceeded
     */
    public function verify(string $plainCode, PasswordHasherInterface $hasher): bool
    {
        if ($this->isExpired()) {
            throw new OTPException(message: "OTP has expired", expired: true);
        }

        if ($this->attempts >= 5) {
            throw new OTPException(message: "Maximum OTP attempts exceeded", expired: true);
        }

        $this->attempts++;

        return $hasher->verify($plainCode, $this->hashCode);
    }

    /**
     * Check if an OTP code is expired.
     */
    public function isExpired(): bool
    {
        $today = new DateTimeImmutable();
        $this->remainingValidityDuration = $today->diff($this->expiresAt);
        
        return $today > $this->expiresAt;
    }

    /**
     * Get remaining validation window in seconds.
     */
    public function getRemainingSeconds(): int
    {
        return max(0, $this->expiresAt->getTimestamp() - time());
    }

    /**
     * Get associated account email.
     */
    public function getEmail(): ?string 
    {
        return $this->email;
    }

    /**
     * Generate secure numeric token string.
     */
    private static function generateCode(int $length = 6): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * Assert if code structure respects numeric constraints.
     */
    private static function isValid(string $code): bool
    {
        return preg_match('/^\d{6}$/', $code) === 1;
    }

    // --- Getters ---

    public function getId(): ?string 
    {
        return $this->id;
    }

    public function getHashCode(): ?string 
    {
        return $this->hashCode;
    }

    public function getPurpose(): AccountFlowPurpose 
    {
        return $this->purpose;
    }

    public function getExpiresAt(): DateTimeImmutable 
    {
        return $this->expiresAt;
    }

    public function getAttempts(): int 
    {
        return $this->attempts;
    }
}
