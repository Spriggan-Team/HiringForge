<?php

namespace App\Domain\JobOffer;

use DateTimeImmutable;
use DomainException;


final class JobOffer
{
    private string $id;
    private string $title;
    private array  $content;
    private  array  $categories;
    private JobStatus $status;
    /** @var JobOfferImage[] */
    private array $images = [];
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        string $id,
        string $title,
        array $content,
        array $categories,
        JobStatus $status,
        array $images,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ) {
        $this->id          = $id;
        $this->title       = $title;
        $this->content     = $content;
        $this->status      = $status;
        $this->images       = $images;
        $this->categories  = $categories;
        $this->createdAt   = $createdAt;
        $this->updatedAt   = $updatedAt;
    }

    // Create an offer
    public static function create(
        string $id,
        string $title,
        array  $content,
        array  $categories = [],
        ?JobStatus $status = null,
        array $images =  [],
    ): self
    {
        if ($id === '') {
            throw new DomainException("JobOffer id cannot be empty");
        }

        if (strlen(trim($title)) < 10) {
            throw new DomainException("Job offer title must be at least 10 characters");
        }

        if (empty($content)) {
            throw new DomainException("Job offer must have content");
        }

        $now = new DateTimeImmutable();

        return new self(
            id: $id,
            title: $title,
            content: $content,
            categories: $categories,
            status: $status ?? JobStatus::DRAFT,
            images: $images,
            createdAt: $now,
            updatedAt: $now
        );
    }

    // -------------------- Business behaviors --------------------


    public function publish(): void
    {
        if ($this->status == JobStatus::PUBLISHED) {
            throw new DomainException("Job offer already published");
        }

        $this->status = JobStatus::PUBLISHED;
        $this->touch();
    }


    public function rename(string $newTitle): void
    {
        if ($this->status === JobStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be renamed");
        }

        if (strlen(trim($newTitle)) < 10) {
            throw new DomainException("Job offer title must be at least 10 characters");
        }

        $this->title = $newTitle;
        $this->touch();
    }


    public function addImage(JobOfferImage $jobImage): void
    {
        if ($this->status === JobStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be renamed");
        }
        $jobImage->media->mustBe(sizeLimitation: 15728640  ); //15 mo
        foreach($this->images as $image){
            if($image->isMain)
                 throw new DomainException(" Two file be set as 'main' for a given jobOffer ");
        }
        $this->images[] = $jobImage;
        $this->touch();
    }


    public function removeImage(JobOfferImage $JobImage): void
    {
        if ($this->status === JobStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be renamed");
        }
        $this->images = array_filter($this->images, fn($image) => $image->media->name === $JobImage->media->name); //Not reindexed
        $this->touch();
    }

    
    public function changeContent(array $newContent): void
    {
        if ($this->status === JobStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be edited");
        }

        if (empty($newContent)) {
            throw new DomainException("Job offer content cannot be empty");
        }

        $this->content = $newContent;
        $this->touch();
    }


    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }


    public function isPublished(): bool { return $this->status === JobStatus::PUBLISHED; }
    
    // -------------------- Getters --------------------

    public function id(): string { return $this->id; }
    public function title(): string { return $this->title; }
    public function content(): array { return $this->content; }
    public function status():    JobStatus {return $this->status;}
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
    public function categories(): array { return $this->categories;  } 
}
