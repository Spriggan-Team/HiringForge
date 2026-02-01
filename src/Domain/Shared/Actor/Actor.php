<?php

namespace App\Domain\Shared\Actor;

interface Actor
{
    public function id(): string;
    public function email(): string;
    public function passwordHash(): string;
}