<?php

namespace App\Domain\File;

enum MediaPurpose: string
{
    case PROFILE = "profile";
    case CV = "attachement";
    case MESSAGE_ATTACHMENT = 'message_attachment';
    case JOB_OFFER_IMAGE = 'job_offer_images';
}