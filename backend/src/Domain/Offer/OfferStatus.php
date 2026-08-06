<?php

namespace  App\Domain\Offer;

enum OfferStatus: string
{
    case SENT = 'SENT';
    case DECLINED = 'DECLINED';
    case ACCEPTED = 'ACCEPTED';
    case EXPIRED = 'EXPIRED';
}