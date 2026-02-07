<?php

namespace App\Application\Command\Usecase\Auth;

use App\Application\DTO\AuthentificateActor;
use App\Domain\Agent\AgentRepositoryInterface;
use App\Domain\Shared\PasswordHasherInterface;

class AgentAuthentificator
{
    public function __construct(
        private AgentRepositoryInterface $repository,
        private PasswordHasherInterface $hasher
    ){}

    /**
     * This is an usecase that enforce buisness login rules
     * @return ?string the identifier of an actor (user, agent, candidate...ect)
     */
    public function execute(AuthentificateActor $actor): ?string
    {
        $agent = $this->repository->findByEmail($actor->email);
        if($agent && $this->hasher->verify($actor->password, $agent->passwordHash())){
            return $agent->id();
        }
        return null;
    }
}