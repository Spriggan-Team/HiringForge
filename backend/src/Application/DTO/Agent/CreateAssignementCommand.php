<?php

namespace App\Application\DTO\Agent;

class CreateAssignementCommand{
    public function __construct(
        public string $agent_id, 
    ){}
}