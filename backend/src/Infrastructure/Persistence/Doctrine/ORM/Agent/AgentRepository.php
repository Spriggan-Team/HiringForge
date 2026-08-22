<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Agent;

use App\Domain\Agent\Agent;
use App\Domain\Agent\AgentRepositoryInterface;
use App\Domain\Shared\KnownIdentity;
use Override;

class AgentRepository implements AgentRepositoryInterface{
    #[Override]
    public function exists(?string $uuid = null, ?string $email = null): KnownIdentity
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function findByEmail(string $email): Agent
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function findById(string $uuid): Agent
    {
        throw new \Exception('Not implemented');
    }


    #[Override]
    public function verifyOwnerShip(string $authorId, string $agentId): void
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function save(Agent $agent): void
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function delete(string $id): void
    {
        throw new \Exception('Not implemented');
    }
}