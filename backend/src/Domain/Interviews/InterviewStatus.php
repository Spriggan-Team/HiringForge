<?php

namespace App\Domain\Interviews;

enum InterviewStatus: string
{
    case CLOSED = "closed"; //-- Manually closed/cancelled by recruiter or rejected by candidate
    case MISSED = "missed";
    case SCHEDULED = "scheduled";
    case IN_PROGRESS = "in_progress";
    case COMPLETED = "completed";
}