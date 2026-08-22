<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Company;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use Doctrine\ORM\Mapping as ORM;


/**
 * This one is used to add multiple images to an user (the inverse is also possible)
 * It is a join table responsable to create a N-N relation between User and Image
 */
#[ORM\Entity()]
#[ORM\Table(
      name: "company_images",
      uniqueConstraints: [
            new ORM\UniqueConstraint(name: "uniq_company_image", columns: ["company_id", 'image_id'])
      ]
    )
]
class CompanyImageEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    /**-----------------------
     * Relations
     ---------------------------*/

    #[ORM\ManyToOne(
        targetEntity: FileEntity::class,
        inversedBy: "companyImages",
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(nullable: false)]
    private FileEntity $image;

    #[ORM\ManyToOne(
        targetEntity: CompanyEntity::class,
        inversedBy: "companyImages",
    )]
    #[ORM\JoinColumn(nullable: false)]
    private CompanyEntity $company;


    //-------------------
    //  Constructions...
    //-------------------

    public function __construct(CompanyEntity $user, FileEntity $image)
    {
        $this->company = $user;
        $this->image= $image;
    }

    //-------------------------
    //  GETTERS
    //------------------------


    public function getId(): int
    {
        return $this->id;
    }


    public function getImage(): FileEntity
    {
        return $this->image;
    }
}