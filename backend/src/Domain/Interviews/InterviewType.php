<?php

namespace App\Domain\Interviews;

enum InterviewType: string
{
    case RH_INTERVIEWS = 'rh_interviews';
    case TECHNICAL_INTERVIEWS = "technical_interviews";
}