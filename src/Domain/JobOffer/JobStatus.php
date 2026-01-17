<?php

namespace App\Domain\JobOffer;

enum JobStatus: string
{
    case DRAFT = 'draft';
    case CLOSED = 'closed';
    case PUBLISHED = 'published';
}