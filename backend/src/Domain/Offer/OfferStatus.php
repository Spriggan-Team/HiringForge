<?php

namespace  App\Domain\Offer;

enum OfferStatus: string
{
    case DRAFT = 'DRAFT';
    case SENT = 'SENT';
    case DECLINED = 'DECLINED';
    case ACCEPTED = 'ACCEPTED';
    case EXPIRED = 'EXPIRED';
    case CANCELLED = 'CANCEL';
}