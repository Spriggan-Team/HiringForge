<?php

namespace App\Application\Command\Usecase\Account;

use App\Api\DTO\Account\MutateAccountRequest;
use App\Domain\ValueObject\MergeRule;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\AccountRepository;

/**
 * Deal with patch request directed toward account 
 */

class AccountModifier
{
    public function __construct(private AccountRepository $repository ){}

    public function execute(MutateAccountRequest $command): void
    {
        $account = $this->repository->getById($command->uuid);
        
        if($command->email){
            $account->setEmail($command->email);
        }

        if($command->password){
            $account->setPassword($command->password);
        }

        $this->repository->save($account, MergeRule::PARTIAL_MERGE );
    }
}