<?php


namespace App\Domain\Candidate;

enum CandidateStatus: string
{
    case INACTIVE = 'inactive';
    case ACTIVE = 'active';
    case HIRED = 'hired';
    case DESPERATE = 'desperate';
}