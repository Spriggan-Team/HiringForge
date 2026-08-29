<?php

namespace  App\Domain\EmploymentOffer;

enum EmploymentOfferStatus: string
{
    case DRAFT = 'DRAFT';
    case SENT = 'SENT';
    case DECLINED = 'DECLINED';
    case ACCEPTED = 'ACCEPTED';
    case EXPIRED = 'EXPIRED';
}