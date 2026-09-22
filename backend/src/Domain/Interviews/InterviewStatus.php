<?php

namespace App\Domain\Interviews;

enum InterviewStatus: string
{
    case CLOSED = "closed"; //-- Manually closed/cancelled by recruiter
    case MISSED = "missed";
    case SCHEDULED = "scheduled";
    case IN_PROGRESS = "in_progress";
    case COMPLETED = "completed";
}