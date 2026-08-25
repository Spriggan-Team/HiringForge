<?php

namespace App\Domain\Notification;


enum NotificationType: string
{
    case JOB_APPLIED = 'JOB_APPLIED';

    case JOB_APPLICATIONS_STATUS_SHIFT = 'job_status_shift';
    //-- Recipient: Candidate
    case INTERVIEW_SCHEDULED = 'INTERVIEW_SCHEDULED';
    case CANDIDATE_REJECTED = 'CANDIDATE_REJECTED';
    
    //-- Recipeint  HYBRID/GENERAL
    case SYSTEM_ALERT = 'SYSTEM_ALERT';
}