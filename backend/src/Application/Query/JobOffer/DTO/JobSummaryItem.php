<?php

namespace App\Application\Query\JobOffer\DTO;

use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobPublicationStatus;

class JobSummaryItem
{
    public string $id;
    public string $title;
    public string $address;

    public string $publicationStatus;
    public ?string $activityStatus = null;

    /**
     * @var array{
     *     candidates: int,
     *     interviews: int,
     *     offers: int,
     *     hired: int
     * }
     */
    public array $cardinal;

    public function __construct(
        string $id,
        string $title,
        string $address,
        string $publicationStatus,
        ?string $activityStatus = null,
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