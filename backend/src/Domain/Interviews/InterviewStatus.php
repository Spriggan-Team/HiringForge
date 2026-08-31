<?php

namespace App\Domain\Interviews;

enum InterviewStatus: string
{
    case CLOSED = "closed"; //-- cancel, closed ...
    case MISSED = "missed";
    case SCHEDULED = "scheduled";
    case IN_PROGRESS = "in_progress";
    case COMPLETED = "completed";
}