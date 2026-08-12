<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper;

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
}