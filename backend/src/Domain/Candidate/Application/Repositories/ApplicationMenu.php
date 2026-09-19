<?php


namespace App\Domain\Candidate\Application\Repositories;

enum ApplicationMenu: string
{
    case ALL = "ALL";               //-- everything
    case PENDING = "PENDING";       //-- at least one employment offer
    case INTERVIEW = "INTERVIEW";   //-- at least one interview
    case COMPLETED = "COMPLETED";   //-- concluded with employment offer
}