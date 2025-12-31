<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;


final class Post
{
    private string $id;
    private string $title;
    private array $content;
    private array $snapshot;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;


    public function __construct(
        string $id,
        string $title,
        array $content,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
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
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;

        $this->takeSnapshot();
    }

    private function takeSnapshot(): void
    {
        $this->snapshot = [
            'title'      => $this->title,
            'content'    => $this->content,
        ];
    }

    public function hasChanged(string $field)
    {
        if (!array_key_exists($field, $this->snapshot) && array_key_exists($field, $this->snapshot)) {
            throw new \InvalidArgumentException("Unknown field $field");
        }
        return $this->$field !== $this->snapshot[$field];
    }

    //  --------------- Business access

    public function getId(): string { return $this->id; }

    public function getTitle(): string { return $this->title; }

    public function getContent(): array { return $this->content; }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }

    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    // ---------------- Business change ----------------setContent
    public function setTitle(string $title):void { $this->title = $title; }

    public function setContent(array $content):void { $this->content = $content; }
}
