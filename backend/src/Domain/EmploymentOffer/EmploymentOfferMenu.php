<?php

namespace  App\Domain\EmploymentOffer;

enum EmploymentOfferMenu: string
{
    case ALL = 'ALL';
    case PENDING = 'PENDING';
    case ACCEPTED = 'ACCEPTED';
    case COMPLETED = 'COMPLETED';
    case REJECTED = 'REJECTED';
}