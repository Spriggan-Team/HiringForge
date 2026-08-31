<?php


namespace App\Application\Query\JobOffer\DTO;

final  class JobOfferViewLightModel
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public string $id,
        public string $title,
        public ?string $image,
        public int $candidates,
        public int $interviews,
        public array $tags,
        public float $treatmentProgress,
        public int $remainingCandidates,
        public string $delay,
    ) {
    }
}