<?php

namespace App\Domain\Shared;


interface TransactionManagerInterface{
    /**
     * @throws \Exception
     */
    public function execute(callable $callback): mixed;
}