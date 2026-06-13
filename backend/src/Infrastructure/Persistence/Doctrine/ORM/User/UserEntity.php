<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Domain\User\User;
use App\Domain\User\UserRole;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;



#[ORM\Entity]
#[ORM\Table(name: "user")]
class UserEntity extends AccountEntity
{
    //------------------------
    // Extra  Columns
    //-----------------------


    //------------------------
    //  Relations
    //-----------------------


    #[ORM\OneToMany(
        mappedBy: "user",
        targetEntity: JobOfferEntity::class
    )]
    private ?Collection $jobOffers = null;


    #[ORM\ManyToOne(inversedBy: "recruiters", targetEntity: CompanyEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompanyEntity $company = null;

    #[ORM\Column(nullable: false, enumType: UserRole::class)]
    private UserRole $userRole = UserRole::RECRUITER;


    //------------------------
    //  Construction...
    //-----------------------
    
    public function __construct()
    {
        parent::__construct();
        $this->jobOffers = new ArrayCollection();
    }

    public static function create(
        string $id,
        string $email,
        string $firstName,
        string $lastName,
        string $password,
        CompanyEntity $company,
        ?string $description = null,
        ?UserRole $userRole = null,
    ): self {
        $entity = new self();
        
        //-- assignement
        $entity->id = $id;
        $entity->lastName = $lastName;
        $entity->firstName = $firstName;

        $entity->email = $email;
        $entity->password = $password;

        $entity->company = $company;
        $entity->description = $description;
        $entity->userRole = $userRole;

        return $entity;
    }

    /* =======================
     * GETTERS
     * ======================= */

    public function getJobOffer(): Collection { return $this->jobOffers; }

    public function getCompany() : CompanyEntity {
        return $this->company;
    }

    public function getUserRole(): UserRole{
        return $this->userRole;
    }

    /* =======================
     * SETTERS
     * ======================= */

    public function setCompany(CompanyEntity $company): static{
        $this->company = $company;
        return $this;
    }

    public function setUserRole(UserRole $userRole) : static {
        $this->userRole = $userRole;
        return $this;
    }

}
