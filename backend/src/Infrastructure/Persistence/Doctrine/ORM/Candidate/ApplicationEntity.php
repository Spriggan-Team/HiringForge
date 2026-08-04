<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Domain\Candidate\Application\JobApplicationStatus;
use Doctrine\ORM\Mapping as ORM;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(
        name: "application",
        uniqueConstraints: [
            new ORM\UniqueConstraint(
                name: "uniq_application",
                columns: ["candidate_id", "job_offer_id"]
            )
        ]    
   )
]
class ApplicationEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $matchScore = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $appliedAt;

    #[ORM\Column(type: 'boolean')]
    private JobApplicationStatus $status = JobApplicationStatus::APPLIED;


    //----------------------------
    //----- RELATIONS
    //----------------------------

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false, name: "candidate_id")]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false, name: "job_offer_id")]
    private JobOfferEntity $jobOffer;
   
    //----------------------------
    //----- Constructing
    //----------------------------
    
    
    public function __construct(
        string $id,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        ?float $correlation = null
    ) {
        $this->id = $id;
        $this->candidate = $candidate;
        $this->jobOffer = $jobOffer;
        $this->matchScore = $correlation;
        $this->status = JobApplicationStatus::APPLIED;
        $this->appliedAt = new \DateTimeImmutable();
    }

    public static function create(
        string $id,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        ?float $correlation = null
    ): self {
        return new self(
            id: $id,
            candidate: $candidate,
            jobOffer: $jobOffer,
            correlation: $correlation
        );
    }

    //==========================
    //    GETTERS
    //==========================

    public function getId():string { return $this->id; }

    public function getCandidate(): CandidateEntity
    {
        return $this->candidate;
    }

    public function getJobOffer(): JobOfferEntity
    {
        return $this->jobOffer;
    }

    public function getAppliedAt()
    {
        return $this->appliedAt;
    }

    public function getStatus(){
        return $this->status;
    }

    public function getMatchScore(){
        return $this->matchScore;
    }

    //==========================
    //    SETTERS
    //==========================

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setStatus(JobApplicationStatus $status){
        $this->status = $status;
        return $this;
    }
}
