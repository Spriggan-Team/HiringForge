<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;


final class Post
{
    private string $id;
    private string $title;
    private array $content;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $title,
        array $content
    ) {
        if ($id === '') {
            throw new InvalidArgumentException('Post id cannot be empty.');
        }

        if (trim($title) === '') {
            throw new InvalidArgumentException('Post title cannot be empty.');
        }

        if ($content === []) {
            throw new InvalidArgumentException('Post content cannot be empty.');
        }

        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }



    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function content(): array
    {
        return $this->content;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
