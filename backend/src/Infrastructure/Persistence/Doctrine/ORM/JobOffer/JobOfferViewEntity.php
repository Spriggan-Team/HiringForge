<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity]
#[ORM\Table(name: "job_offer_views")]
class JobOfferViewEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CandidateEntity::class)]
    #[ORM\JoinColumn(nullable: false, name: "candidate_id")]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(
        targetEntity: JobOfferEntity::class,
        inversedBy: "views"
    )]
    #[ORM\JoinColumn(nullable: false)]
    private JobOfferEntity $jobOffer;

    #[ORM\Column]
    private \DateTimeImmutable $viewedAt;

    public function __construct(
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer
    ) {
        $this->candidate = $candidate;
        $this->jobOffer = $jobOffer;
        $this->viewedAt = new \DateTimeImmutable();
    }


    //---------------------
    //- GETTERS
    //---------------------

    public function getId(): int{
        return $this->id;
    }

    public function getUser(): CandidateEntity {
        return $this->candidate;
    }

    public function getJobOffer() : JobOfferEntity {
        return $this->jobOffer;
    }

    public function getViewedAt(): \DateTimeImmutable {
        return $this->viewedAt;
    }

    //------------------------
    //--- SETTERS
    //------------------------

    public function setUser(CandidateEntity $user): self{
        $this->candidate = $user;
        return $this;
    }

    public function setJobOffer(JobOfferEntity $jobOffer): self{
        $this->jobOffer = $jobOffer;
        return $this;
    }
}