<?php

namespace App\Application\Usecases\Agent;

use App\Domain\Agent\Agent;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Application\DTO\Agent\CreateAgentCommand;

use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Agent\AgentRepositoryInterface;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;

use App\Domain\OTP\Exceptions\OTPException;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Exception\RessourceAlreadyRegistered;

use App\Domain\Shared\PlainPassword;
use App\Domain\User\UserRepositoryInterface;



class CreateAgentUseCase
{
    public function __construct(
        private AccountRepositoryInterface $repository,
        private AgentRepositoryInterface $agentRepository,
        private UserRepositoryInterface $userRepository,
        private OTPRepositoryInterface $OTPRepository,
        private PasswordHasherInterface $hasher,
    ){}

    
    /** Create an agent */
    public function execute(CreateAgentCommand $command) : Agent {
        $email = EmailAddress::create($command->email);
        
        //-- check ressource in bdd
        try{
            $identity = $this->repository->exists(email: $email->value());
            if($identity)
                throw new RessourceAlreadyRegistered();
        }
        catch(\Exception){}

        //-- check author existence (user)
        $this->repository->exists(uuid: $command->authorId);

        $otp = null;
        //-- Verify otp code verification
        try{
            $otp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
                email: $email->value(),
                purpose: AccountFlowPurpose::CONFIRM_AGENT_IDENTITY
            );
        }
        catch(RessourceNotFound){ throw new OTPException(isInvalid: true); }

        if($otp && !$otp->verify($command->verificationCode, $this->hasher))
            throw new OTPException(isInvalid: true);

        $agent = new Agent(
            email: $email->value(), 
            passwordHash: $this->hasher->hash(new PlainPassword($command->password)->value()),
            authorId: $command->authorId
        );
        $this->agentRepository->save($agent);
        
        return $agent;
    }
}