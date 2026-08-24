<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global;


use App\Domain\Shared\TransactionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

use Override;

class DoctrineTransactionManager implements TransactionManagerInterface{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function execute(callable $callback): mixed
    {
        return $this->em->wrapInTransaction($callback);
    }
}
