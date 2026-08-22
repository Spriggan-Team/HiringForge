<?php

namespace App\Domain\Shared;


use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageParams;
use App\Domain\File\MediaStorageScope;


final class AccountStorageParams
{
    public static function create(
        string $ownerId,
        MediaOwnerType $ownerType,
        MediaPurpose $purpose,
        MediaStorageScope $scope,
        ?string $storedFileName = null,
    ){
        return new MediaStorageParams(
            ownerId: $ownerId,
            ownerType: $ownerType,
            purpose: $purpose,
            scope: $scope,
            storedFileName: $storedFileName
        );
    }


    public static function resumes(
        string $candidateId,
        ?string $storedFileName = null,
    ): MediaStorageParams {
        return new MediaStorageParams(
            ownerId: $candidateId,
            ownerType: MediaOwnerType::CANDIDATE,
            purpose: MediaPurpose::CV,
            scope: MediaStorageScope::PRIVATE,
            storedFileName: $storedFileName,
        );
    }

    public static function candidateProfileImage(
        string $candidateId,
        ?string $storedFileName = null,
    ): MediaStorageParams {
        return new MediaStorageParams(
            ownerId: $candidateId,
            ownerType: MediaOwnerType::CANDIDATE,
            purpose: MediaPurpose::PROFILE,
            scope: MediaStorageScope::PRIVATE,
            storedFileName: $storedFileName,
        );
    }

    public static function companyVideoPresentation(
        string $companyId,
        ?string $storedFileName = null
    ): MediaStorageParams{
        return new MediaStorageParams(
            ownerId: $companyId,
            storedFileName: $storedFileName,
            purpose: MediaPurpose::PROFILE,
            ownerType: MediaOwnerType::COMPANY,
        );
    }

    public static function companyLogo(
        string $companyId,
        ?string $storedFileName =null
    ): MediaStorageParams{
        return new MediaStorageParams(
            ownerId: $companyId,
            storedFileName: $storedFileName,
            purpose: MediaPurpose::PROFILE,
            ownerType: MediaOwnerType::COMPANY,
        );
    }

    public static function companyImages(
        string $companyId,
        ?string $storedFileName = null
    ): MediaStorageParams{
        return new MediaStorageParams(
            ownerId: $companyId,
            storedFileName: $storedFileName,
            purpose: MediaPurpose::PROFILE,
            ownerType: MediaOwnerType::COMPANY,
        );
    }

    public static function recruiterProfile(
        string $companyId,
        ?string $storedFileName = null
    ): MediaStorageParams{
        return new MediaStorageParams(
            ownerId: $companyId,
            storedFileName: $storedFileName,
            purpose: MediaPurpose::PROFILE,
            ownerType: MediaOwnerType::USER,
        );
    }

    public static function companyJobImages(
        string $companyId,
        ?string $storedFileName = null
    ): MediaStorageParams{
        return new MediaStorageParams(
            ownerId: $companyId,
            ownerType: MediaOwnerType::COMPANY,
            storedFileName: $storedFileName,
            purpose: MediaPurpose::JOB_OFFER_IMAGE,
        );
    }
}