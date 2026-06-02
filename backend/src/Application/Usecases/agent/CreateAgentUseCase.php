<?php

namespace App\Application\Usecases\Agent;

use App\Domain\Agent\Agent;
use App\Application\DTO\Agent\CreateAgentCommand;

use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Agent\AgentRepositoryInterface;
use App\Infrastructure\Security\PasswordHasher;

class CreateAgentUseCase
{
    public function __construct(
        private AgentRepositoryInterface $AgentRepository,
        private OTPRepositoryInterface $OTPRepository,
        private PasswordHasher $hasher,
    ){}

    public function execute(CreateAgentCommand $command) : Agent {
        
    }
}