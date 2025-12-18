<?php

namespace App\Domain\Model;



use DateTimeImmutable;

final class Post
{
    private ?string $id;
    private string $title;
    private array $content;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        ?string $id,
        string $title,
        array $content,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }


    public static function fromArray(array $data): self
    {
        return new self(
            null,
            $data['title'],
            $data['content'],
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );
    }
}
