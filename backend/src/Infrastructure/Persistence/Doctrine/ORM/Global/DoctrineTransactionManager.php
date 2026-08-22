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
        $this->em->beginTransaction();

        try {
            $result = $callback();

            $this->em->flush();
            $this->em->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}