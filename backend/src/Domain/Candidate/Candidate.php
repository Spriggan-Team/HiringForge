<?php

namespace App\Domain\Candidate;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\Account;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\Skill\BasicSkillModel;
use App\Domain\Shared\Skill\SkillsDiff;

class Candidate extends Account
{
    /** @var array<int, StaticMedia> $cvs all resume */
    private array $cvs = [];

    /** @var array<int,BasicSkillModel> $skills */
    private array $skills = [];

    private CandidateStatus $status = CandidateStatus::ACTIVE;

    private ?Address $address;
    private int $searchRadius = 10; //default search radius on map


    private function __construct(
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?StaticMedia $image = null,
        array $cvs = [],
        array $skills = [],
        ?string $id = null, 
        ?Address $address = null,
        ?int $searchRadius = null,
        ?string $description = null
    ){
        $this->id = $id ?? CandidateId::create()->value();

        parent::__construct(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            description: $description,
            email: $email,
            passwordHash: $passwordHash,
            image: $image
        );

        $this->cvs = $cvs;
        $this->skills = $skills;

        $this->address = $address;
        if($searchRadius){
            $this->searchRadius = $searchRadius;
        }
    }

    public static function create(
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?string $description = null,
        ?string $id = null, 
        ?StaticMedia $image = null,
        array $cvs = [],
        array $skills = [],
        ?Address $address = null,
        ?int $searchRadius = null,
    ): self
    {
        return new self(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            passwordHash: $passwordHash,
            image: $image,
            description: $description,
            cvs: $cvs,
            skills: $skills,
            address: $address,
            searchRadius: $searchRadius
        );
    }

    //----------------------------
    //   Business access
    //--------------------------



    /** @return array<int, StaticMedia> */
    public function cvs()
    {
        return $this->cvs;
    }


    public function searchRadius(): int
    {
        return $this->searchRadius;
    }

    
    public function address(){
        return $this->address;
    }


    public function skills(){
        return $this->skills;
    }

    //------------------------------------------
    // - Business change --
    //-----------------------------------------

    //-- CV
    public function addCV(?StaticMedia $cv): static
    {
        if ($cv === null) {
            return $this;
        }

        foreach ($this->cvs as $resume) {
            if ($resume->name === $cv->name) {
                throw new \DomainException('Cannot duplicate resume');
            }
        }

        $this->cvs[] = $cv;

        return $this;
    }

    public function removeCv(?StaticMedia $cv = null): static
    {
        if ($cv === null) {
            return $this;
        }
        $this->cvs = array_filter(
            $this->cvs, 
            fn(StaticMedia $resume) => $resume->name !== $cv->name
        );

        return $this;
    }

    //-- Status

    public function changeStatus(CandidateStatus $status){
        $this->status = $status;
    }

    //-- Addres
    
    public function setAddress(Address $address){
        $this->address = $address;
        return $this;
    }

    public function setSearchRadius(int $searchRadius): static
    {
        $this->searchRadius = $searchRadius;
        return $this;
    }


    //-- Skills

    /**
     * perform adding and removal 
     * @param BasicSkillModel[] $skills
     */
    public function skillsDiff(array $skills): SkillsDiff
    {
        $currentSkills = [];

        foreach ($this->skills as $skill) {
            $currentSkills[$skill->id()] = $skill;
        }

        $requestedSkills = [];

        foreach ($skills as $skill) {
            $requestedSkills[$skill->id()] = $skill;
        }

        $added = [];
        $removed = [];
        $unchanged = [];

        // Skills submitted by the user
        foreach ($requestedSkills as $skillId => $skill) {
            if (isset($currentSkills[$skillId])) {
                $unchanged[] = $skill;
            } else {
                $added[] = $skill;
            }
        }

        // Skills currently included but missing from the new list
        foreach ($currentSkills as $skillId => $skill) {
            if (!isset($requestedSkills[$skillId])) {
                $removed[] = $skill;
            }
        }

        return new SkillsDiff(
            added: $added,
            removed: $removed,
            unchanged: $unchanged,
        );
    }
}