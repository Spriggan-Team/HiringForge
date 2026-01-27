<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(
      name: "user_image",
      uniqueConstraints: [
            new ORM\UniqueConstraint(name: "uniq_user_image", columns: ["user_id", 'image_id'])
      ]
    )
]
class UserImageEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ImageEntity::class, inversedBy: "userImages", cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ImageEntity $image;

    #[ORM\ManyToOne(targetEntity: UserEntity::class, inversedBy: "userImages", cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private UserEntity $user;


    //-------------------------
    //  GETTERS
    //------------------------


    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function getImage(): ImageEntity
    {
        return $this->image;
    }

    //-------------------------
    //  SETTERS
    //------------------------

    public function  attachToUser(UserEntity $user) : static 
    {
        $this->user = $user;
        return $this;    
    }

    public function  attachToImage(ImageEntity $image) : static 
    {
        $this->image = $image;
        return $this;    
    }
}