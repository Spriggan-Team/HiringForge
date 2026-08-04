<?php

namespace App\Domain\Notification;


enum NotificationType: string
{
    case JOB_APPLIED = 'JOB_APPLIED';

    //-- Recipient: Candidate
    case INTERVIEW_SCHEDULED = 'INTERVIEW_SCHEDULED';
    case CANDIDATE_REJECTED = 'CANDIDATE_REJECTED';
    
    //-- Recipeint  HYBRID/GENERAL
    case SYSTEM_ALERT = 'SYSTEM_ALERT';
}