<?php

namespace App\Application\Query\JobOffer\DTO;

use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobOffer;

class JobSummaryItem
{
    public string $id;
    public string $title;
    public string $address;

    public JobOffer $publicationStatus;
    public ?JobActivityStatus $activityStatus = null;

    /** @var array<mixed> */
    public array $cardinal;

    public function __construct(
        string $id,
        string $title,
        string $address,
        JobOffer $publicationStatus,
        ?JobActivityStatus $activityStatus = null,
        array $cardinal = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->address = $address;
        $this->publicationStatus = $publicationStatus;
        $this->activityStatus = $activityStatus;
        $this->cardinal = $cardinal;
    }
}