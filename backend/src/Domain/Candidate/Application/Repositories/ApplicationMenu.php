<?php


namespace App\Domain\Candidate\Application\Repositories;

enum ApplicationMenu: string
{
    case ALL = "ALL";
    case PENDING = "PENDING";
    case INTERVIEW = "INTERVIEW";
    case COMPLETED = "COMPLETED";
}