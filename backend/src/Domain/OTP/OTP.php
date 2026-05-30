<?php

namespace App\Domain\OTP;

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
        int $attempts = 0,
        ?string $plainCode = null
    ): self {
        // Generate code if not provided
        $plainCode = $plainCode ?? self::generateCode();

        // Validation format
        if (!self::isValid($plainCode)) {
            throw new DomainException("Generated OTP code is invalid");
        }

        // Hash du code
        $hashCode = $hasher->hash($plainCode);

        // TTL based on purpose
        $expiresAt = new DateTimeImmutable();
        switch ($purpose) {
            case AccountFlowPurpose::PASSWORD_RESET:
                $expiresAt = $expiresAt->add(new DateInterval("PT5M")); // 5 minutes
                break;
            default:
                $expiresAt = $expiresAt->add(new DateInterval("PT15M")); // 15 minutes
        }

        return new self($hashCode, $attempts, $expiresAt, $purpose);
    }


    /**
     * This function is not safe!!
     * WARNING: This should only be used when input data data are sure to be safe
     *          cause this function does not enforce domain validation and then is not safe 
     */
    public static function hydrate(
        string $plainCode,
        \DateTimeImmutable $expiresAt,
        AccountFlowPurpose $purpose,
        int $attempts = 0,
    ): self
    {
        return new self(
            $plainCode,
            $attempts,
            $expiresAt,
            $purpose
        );
    }


    /**
     * Verify a plain code against the stored hashed OTP.
     *
     * @param string $plainCode Code provided by the user
     * @param PasswordHasherInterface $hasher Hasher used to verify the code
     *
     * @return bool True if valid
     * @throws DomainException If expired or attempts exceeded
     */
    public function verify(string $plainCode, PasswordHasherInterface $hasher): bool
    {
        if ($this->isExpired()) {
            throw new DomainException("OTP has expired");
        }

        if ($this->attempts >= 5) {
            throw new DomainException("Maximum OTP attempts exceeded");
        }

        $this->attempts++;

        return $hasher->verify($plainCode, $this->hashCode);
    }

    // --- Getters ---

    /**
     * Check  if an otp code is expired or not
     * return true if it is, not otherwise
     * @return bool
     */
    public function isExpired(): bool
    {
        $today = new DateTimeImmutable();

        $this->remainingValidityDuration = $today->diff($this->expiresAt);
        return $today > $this->expiresAt;
    }

    public function getRemainingSeconds(): int
    {
        return max(
            0,
            $this->expiresAt->getTimestamp()
            - time()
        );
    }

    
    // --- Utilitaires internes ---
    private static function generateCode(int $length = 6): string
    {
        return (string) random_int(100000, 999999); // 6-digit OTP
    }


    private static function isValid(string $code): bool
    {
        return preg_match('/^\d{6}$/', $code) === 1;
    }

}
