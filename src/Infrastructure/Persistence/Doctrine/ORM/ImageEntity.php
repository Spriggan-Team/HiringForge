<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
        name: 'image',
    )
]
class ImageEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private string $originalName;

    #[ORM\Column()]
    private string $mime;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $size;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;


    #[ORM\OneToMany(mappedBy: "image", targetEntity: UserImageEntity::class, cascade:['persist', 'remove'])]
    private Collection $userImages;


    public function __construct()
    {
        $this->userImages = new ArrayCollection();
    }
    
    /* =======================
     * GETTERS
     * ======================= */
    
    public function getId(){
        return $this->id;
    }
    
    public function getOriginalName() {
        return $this->originalName;
    }
    

    
    public function getMime(){
        return $this->mime;
    }

    public function getSize(){
        return $this->size;
    }
    public function getCreatedAt(){
        return $this->createdAt;
    }


    //------Collection

    public function getUserImages()
    {
        return $this->userImages;
    }
    
    /* =======================
     * SETTERS
     * ======================= */
    public function setOriginalName(string $originalName):static
    {
        $this->originalName = $originalName;
        return $this;
    }
    
    public function setMime(string $mime):static
    {
        $this->mime = $mime;
        return $this;
    }    
    
    
    public function setSize(float $size):static
    {
        $this->size = $size;
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt):static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

}