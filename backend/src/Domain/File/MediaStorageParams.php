<?php


namespace App\Domain\File;


final class MediaStorageParams
{
    public function __construct(
        public readonly string $ownerId,
        public readonly MediaOwnerType $ownerType,
        public readonly MediaPurpose $purpose,
        public readonly MediaStorageScope $scope = MediaStorageScope::PUBLIC,
        public ?string $storedFileName = null,
        
        /**
         * This is the relative path from the src folder ("/path")
         * @var string $storagePrefix
         */
        public string $storagePrefix = '/Vault'
    ) {}
}
