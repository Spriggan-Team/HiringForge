<?php


namespace App\Domain\Notification;

abstract class BaseJobNotificationData
{
    public function __construct(
        public string $jobId,
        public string $jobTitle,
    ){}

    abstract public function getJobId(): string;
    abstract public function getJobTitle(): string;
}