<?php

namespace App\Domain\JobOffer;

enum JobWorkMode: string {
    case REMOTE = "remote";
    case ONSITE = "onsite";
    case HYBRID = "hybrid";
}