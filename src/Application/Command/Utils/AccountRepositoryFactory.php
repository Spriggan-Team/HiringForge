<?php

namespace App\Application\Command\Utils;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\Account\AccountRole;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateRepository;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;


class AccountRepositoryFactory
{
    public function __construct(
        private EntityManagerInterface $em,
    ){}

    /**
     * @depracated - Should no longer be used
     * This function is used to choose the specific type of account repository
     * you want to use
     * @param string $type  A value between 'candidate' | 'user'
     * @return AccountRepositoryInterface
     */
    public function create(string $type): void
    {

    }
}