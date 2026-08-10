<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
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
// #[ApiResource(
//     shortName: 'Application',
//     operations: [],
//     graphQlOperations: [
//         new QueryCollection(
//             name: 'getCollection',
//             security: "is_granted('ROLE_USER') or is_granted('ROLE_COMPANY_ADMIN')",
//             securityMessage: "Seuls les recuteurs peuvent consulter cette projection de candidatures."
//         ),
//         new Query(
//             name: 'getItem',
//             security: "is_granted('ROLE_USER') or is_granted('ROLE_COMPANY_ADMIN')"
//         )
//     ]
// )]
class ApplicationEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $matchScore = null;
    
    #[ORM\Column(type: 'boolean')]
    private JobApplicationStatus $status = JobApplicationStatus::APPLIED;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $appliedAt;
    

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    //----------------------------
    //----- RELATIONS
    //----------------------------

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false, name: "candidate_id")]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false, name: "job_offer_id")]
    private JobOfferEntity $jobOffer;
    
    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false, name: "company_id")]
    private CompanyEntity $company;
   
    //----------------------------
    //----- Constructing
    //----------------------------
    
    
    public function __construct(
        string $id,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        CompanyEntity $company,
        ?float $matchScore = null
    ) {
        $this->id = $id;
        $this->candidate = $candidate;
        $this->jobOffer = $jobOffer;
        $this->matchScore = $matchScore;
        $this->company = $company;
        $this->status = JobApplicationStatus::APPLIED;
        $this->appliedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        string $id,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        CompanyEntity $company,
        ?float $matchScore = null
    ): self {
        return new self(
            id: $id,
            candidate: $candidate,
            jobOffer: $jobOffer,
            matchScore: $matchScore,
            company: $company
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

    public function getCompany(){
        return $this->company;
    }

    public function getUpdatedAt(){
        return $this->updatedAt;
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

    public function touch()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
