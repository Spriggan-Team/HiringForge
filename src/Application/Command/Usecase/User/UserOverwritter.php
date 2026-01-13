<?php


namespace App\Application\Command\Usecase\Account;


use App\Domain\ValueObject\MergeRule;
use App\Api\DTO\User\OverwriteUserRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\UserRepository;

class UserOverwritter
{
    public function __construct(private UserRepository $repository){}

    public function execute(OverwriteUserRequest $command):void
    {
        $user = $this->repository->getById($command->uuid);
        $this->repository->save($user, MergeRule::FULL_OVERWRITE);
    }
}