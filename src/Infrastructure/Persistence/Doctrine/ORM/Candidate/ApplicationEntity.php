<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use Doctrine\ORM\Mapping as ORM;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;


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

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false, name: "candidate_id")]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false, name: "job_offer_id")]
    private JobOfferEntity $jobOffer;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $appliedAt;


    public function __construct(
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer
    ) {
        $this->candidate =$candidate;
        $this->jobOffer = $jobOffer;
        $this->appliedAt = new \DateTimeImmutable();
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

    //==========================
    //    SETTERS
    //==========================

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }
}
