<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Repositories;

use App\Api\Responder\ApiResponse;
use App\Domain\File\Media;
use App\Domain\File\FileRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper\FileEntityMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


use Override;


class FileRepository 
    extends ServiceEntityRepository 
    implements FileRepositoryInterface
{
    public function __construct(
        private ManagerRegistry $manager,
        private FileEntityMapper $mapper,
    ){
        parent::__construct($manager, FileEntity::class);
    }

    #[Override]
    public function save(Media $file): int
    {
        
       $entity = $this->mapper->toFileEntity($file);
       $em = $this->getEntityManager();
       $em->persist($entity);
       $em->flush();
       return $entity->getId();
    }

    #[Override]
    public function delete(int $fileId): void
    {
        $em = $this->getEntityManager();
        $entity = $em->find(FileEntity::class, $fileId);
        if ($entity !== null) {
            $em->remove($entity);
            $em->flush();
        }
    }
}