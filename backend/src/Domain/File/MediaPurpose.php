<?php

namespace App\Domain\File;

enum MediaPurpose: string
{
    case PROFILE = "profile";
    case CV = "attachements";
    case MESSAGE_ATTACHMENT = 'message_attachment';
    case JOB_OFFER_IMAGE = 'job_offer_images';
}