<?php

namespace App\Domain\Interviews;

enum InterviewStatus: string
{
    case CANCEL = "cancel";
    case CLOSED = "closed";
    case MISSED = "missed";
    case SCHEDULED = "scheduled";
    case IN_PROGRESS = "in_progress";
    case COMPLETED = "completed";
}