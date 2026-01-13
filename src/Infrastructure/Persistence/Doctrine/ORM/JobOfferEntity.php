<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity]
#[ORM\Table(name: "JobOffer")]
class JobOfferEntity 
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'json')]
    private array $content = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne( inversedBy: "posts", targetEntity: UserEntity::class )]
    #[ORM\JoinColumn(nullable: false)]
    private UserEntity $account;

    public static function create(
        string $id,
        string $title,
        array  $content,
        UserEntity $account,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): JobOfferEntity
    {
        $entity = new self();
        $entity->setId($id);
        $entity->setTitle($title);
        $entity->setContent($content);
        $entity->setCreatedAt($createdAt);
        $entity->setUpdatedAt($updatedAt);
        $entity->setAccount($account);
        return $entity;
    }

    /* =======================
     * GETTERS
     * ======================= */

    public function getId(): string { return $this->id; }

    public function getTitle(): string { return $this->title; }

    public function getContent(): array { return $this->content; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getAccount(): UserEntity { return $this->account; }

    /* =======================
     * SETTERS
     * ======================= */

    public function setId(string $id): void { $this->id = $id; }

    public function setTitle(string $title): void { $this->title = $title;}

    public function setContent(array $content): void { $this->content = $content; }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void { $this->createdAt = $createdAt; }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void{ $this->updatedAt = $updatedAt; }

    public function setAccount(UserEntity $accountId): void{ $this->account = $accountId; }
}
