<?php

namespace App\Domain\File;

enum FilePurpose: string
{
    case PROFILE_IMAGE = 'profile';
    case POST_IMAGE    = 'post';
    case MESSAGE_ATTACHMENT = 'message_attachment';
}