<?php

namespace App\Domain\Notification;


enum NotificationType: string
{
    case JOB_APPLIED = 'JOB_APPLIED';
    case INTERVIEW_SCHEDULED = 'INTERVIEW_SCHEDULED';
    case CANDIDATE_REJECTED = 'CANDIDATE_REJECTED';
    case SYSTEM_ALERT = 'SYSTEM_ALERT';
}