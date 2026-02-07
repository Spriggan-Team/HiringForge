<?php

namespace App\Domain\File;

enum FilePurpose: string
{
    case PROFILE = "profile";
    case JOB_IMAGE = 'post';
    case CV = "attachement";
    case MESSAGE_ATTACHMENT = 'message_attachment';
}