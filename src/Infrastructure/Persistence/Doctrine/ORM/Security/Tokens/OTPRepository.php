<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens;

use App\Domain\OTP\OTP;
use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;

class OTPRepository implements OTPRepositoryInterface
{
    public function getLastVerificationTokenWithPurpose(string $email, AccountFlowPurpose $purpose): ?OTP
    {
        throw new \Exception('Not implemented');
    }
    
    public function save(string $email, OTP $otp): void
    {
        // $
        // $token = new VerificationTokenEntity();
    }
}