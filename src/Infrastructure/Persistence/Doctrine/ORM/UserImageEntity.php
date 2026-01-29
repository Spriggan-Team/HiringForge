<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;


/**
 * This one is used to add multiple images to an user (the inverse is also possible)
 * It is a join table responsable to create a N-N relation between User and Image
 */
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
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: ImageEntity::class,
        inversedBy: "userImages",
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ImageEntity $image;

    #[ORM\ManyToOne(
        targetEntity: UserEntity::class,
        inversedBy: "userImages",
    )]
    #[ORM\JoinColumn(nullable: false)]
    private UserEntity $user;

    public function __construct(UserEntity $user, ImageEntity $image)
    {
        $this->user = $user;
        $this->image= $image;
    }

    //-------------------------
    //  GETTERS
    //------------------------


    public function getId(): int
    {
        return $this->id;
    }


    public function getImage(): ImageEntity
    {
        return $this->image;
    }
}