<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\CategoryEntity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(
    name: "job_category",
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: "uniq_job_category",
            columns: ["job_offer_id", "category_id"]
        )
    ]
)]
class JobCategoryEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'jobCategories')]
    #[ORM\JoinColumn(nullable: false, name: "category_id")]
    private CategoryEntity $category;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: false, name: "job_offer_id")]
    private JobOfferEntity $jobOffer;

    //-----GETTERS

    public function getId()
    {
        return $this->id;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getJobOffer()
    {
        return $this->jobOffer;
    }

    //-------SETTERS

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setCategory(CategoryEntity $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function setJobOffer(JobOfferEntity $jobOffer): static
    {
        $this->jobOffer = $jobOffer;
        return $this;
    }
}
