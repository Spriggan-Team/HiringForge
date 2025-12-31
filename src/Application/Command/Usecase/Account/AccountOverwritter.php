<?php


namespace App\Application\Command\Usecase\Account;


use App\Domain\ValueObject\MergeRule;
use App\Api\DTO\Account\OverwriteAccountRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\AccountRepository;


class AccountOverwritter
{
    public function __construct(private AccountRepository $repository){}

    public function execute(OverwriteAccountRequest $command):void
    {
        $account = $this->repository->getById($command->uuid);
        $this->repository->save($account, MergeRule::FULL_OVERWRITE);
    }
}