<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account;

use App\Domain\Shared\Account\AccountRole;
use App\Infrastructure\Persistence\Doctrine\ORM\Agent\AgentEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens\OTPVerificationTokenEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\Common\Collections\Collection;


/**
 * This is a class used to simplify Actor definiton (ex: User, Candidate, Agent)
 */
#[ORM\Entity]
#[ORM\Table(name: 'account')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: "account_role", type: 'string')]
#[ORM\DiscriminatorMap([
    AccountRole::USER->value => UserEntity::class,
    AccountRole::AGENT->value => AgentEntity::class,
    AccountRole::CANDIDATE->value => CandidateEntity::class,
])]
class AccountEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    protected string $id;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    protected string $email;

    #[ORM\Column(length: 150)]
    protected string $firstName;

    #[ORM\Column(length: 150)]
    protected string $lastName;

    #[ORM\Column(length: 255, nullable: false)]
    protected string $password;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column]
    protected \DateTimeImmutable $createdAt;

    //--------------------
    //---- Relations
    //--------------------

    #[ORM\OneToOne(
        inversedBy: "accountImage",
        targetEntity: FileEntity::class
    )]
    protected ?FileEntity $image = null;


    #[ORM\OneToMany(
        mappedBy: "account",
        targetEntity: OTPVerificationTokenEntity::class
    )]
    protected ?Collection $otpTokens = null;


    //---------
    //----Object Creating ..
    //-----------
    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
    }



    //---------------
    //  GETTER
    //--------------
    public function getId(): string { return $this->id; }

    public function getFirstName() : string { return $this->firstName; }
    public function getLastName() : string { return $this->lastName; }

    public function getImage() : ?FileEntity {
        return $this->image;
    }

    public function getEmail():string { return $this->email; }

    public function getPassword():string { return $this->password; }

    public function getDescription(): string { return $this->description; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    

    public function getOTPToken() {
        return $this->otpTokens;
    }

    
    public function getAccountType(): AccountRole{
        return match (true) {
            $this instanceof UserEntity => AccountRole::USER,
            $this instanceof AgentEntity => AccountRole::AGENT,
            $this instanceof CandidateEntity => AccountRole::CANDIDATE,
        };
    }



    //---------------------------
    //  SETTERS
    //--------------------------

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }
    
    public function setFirstName(string $firstName): self{
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): self{
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(string $email): static{ 
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $hash): static { 
        $this->password = $hash;
        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setImage(?FileEntity $file): self{
        $this->image = $file;
        return $this;
    }

}