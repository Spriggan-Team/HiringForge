<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper;

use App\Domain\File\Media;
use App\Domain\File\StaticMedia;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;

final class FileEntityMapper
{
    public static function toStaticDomainMedia(FileEntity $file){
        return StaticMedia::hydrate(
            id: $file->getId(),
            name: $file->getName(),
            size: $file->getSize(),
            mime: $file->getMime(),
            originalName: $file->getOriginalName(),
            createdAt: $file->getCreatedAt()
        );
    }

    public static function toFileEntity(Media $file){
        return  FileEntity::create(
            name: $file->name,
            mime: $file->mime,
            size: $file->size,
            originalName: $file->originalName
        );
    }
}