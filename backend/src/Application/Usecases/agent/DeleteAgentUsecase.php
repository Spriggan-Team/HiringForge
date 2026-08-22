<?php

namespace App\Application\Usecases\Agent;


use App\Domain\Agent\AgentRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;



class DeleteAgentUsecase{
    public function __construct(
        private AgentRepositoryInterface $agentRepository,
        private UserRepositoryInterface $userRepository
    ){}
    
    /**
     * Perform secure agent deletion with domain
     * validation
     * @param string $authorId the identifiant of the author of the account (linked to the agent)
     * @param string $agentId  the identifiant of the account to delete
     * @throws \Exception|RessourceNotFound - exception
     */
    public function execute(string $authorId, string $agentId): void
    {
        //-- check existence
        $agent = $this->agentRepository->exists(uuid: $agentId);
        $this->agentRepository->verifyOwnerShip(
            authorId: $authorId,
            agentId: $agent->uuid
        );

        $this->agentRepository->delete($agent->uuid);
    }
}