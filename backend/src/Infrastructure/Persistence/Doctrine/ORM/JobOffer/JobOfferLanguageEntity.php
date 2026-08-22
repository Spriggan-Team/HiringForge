<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;

use App\Domain\Shared\LanguageLevel;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "job_offer_languages")]
class JobOfferLanguageEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "languages")]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobOfferEntity $jobOffer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?LanguageEntity $language = null;

    #[ORM\Column(enumType: LanguageLevel::class)]
    private LanguageLevel $level = LanguageLevel::B2;

 
    public function __construct()
    {}

    public static function create(
        JobOfferEntity $jobOffer,
        LanguageEntity $language,
        LanguageLevel $level
    ): self {
        $entity = new self();

        $entity->jobOffer = $jobOffer;
        $entity->language = $language;
        $entity->level = $level;

        return $entity;
    }

    public static function reconstitute(
        int $id,
        JobOfferEntity $jobOffer,
        LanguageEntity $language,
        LanguageLevel $level
    ): self {
        $entity = new self();

        $entity->id = $id;
        $entity->jobOffer = $jobOffer;
        $entity->language = $language;
        $entity->level = $level;

        return $entity;
    }


    //-----------------
    //-- GETTERS
    //---------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobOffer(): ?JobOfferEntity
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOfferEntity $jobOffer): static
    {
        $this->jobOffer = $jobOffer;

        return $this;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }


    
    //-----------------
    //-- SETTERS
    //---------------

    public function setLanguage(?LanguageEntity $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getLevel(): LanguageLevel
    {
        return $this->level;
    }

    public function setLevel(LanguageLevel $level): static
    {
        $this->level = $level;

        return $this;
    }
}