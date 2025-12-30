<?php

namespace App\Infrastructure\Persistence\MySQL\Doctrine;

use Doctrine\ORM\Mapping as ORM;

use App\Infrastructure\Persistence\MySQL\Doctrine\AccountEntity;


#[ORM\Entity]
#[ORM\Table(name: "Post")]
class PostEntity 
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

    #[ORM\ManyToOne( inversedBy: "posts", targetEntity: AccountEntity::class )]
    private AccountEntity $account;

    /* =======================
     * GETTERS
     * ======================= */

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /* =======================
     * SETTERS
     * ======================= */

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
