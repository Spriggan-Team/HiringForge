<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity]
#[ORM\Table(name: "skills")]
class SkillEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    private string $id; //-- uuid applicatif identifiant

    #[ORM\Column()]
    public string $canonicalName;
    
    #[ORM\Column(unique: true, nullable: true)]
    private ?string $escoUri = null;

    #[ORM\Column(unique: true, nullable: true)]
    private ?string $onetCode = null;


    #[ORM\Column(type: "boolean")]
    private bool $isDefault = true;


    //----------------------
    //--- Relations
    //----------------------


    #[ORM\OneToMany(
        mappedBy: 'skill',
        targetEntity: SkillTranslationEntity::class,
        cascade: ['persist'] // !!IMPORRTANT
    )]
    private Collection $translations;

    //---------------------
    //--- Construct
    //----------------------

    public function __construct(
        string $id,
        string $canonicalName,
        ?string $onetCode = null,
        ?string $escoUri = null,
        bool $isDefault = true,
    ){
        $this->id = $id;
        $this->canonicalName = $canonicalName;
        $this->escoUri = $escoUri;
        $this->onetCode = $onetCode;
        $this->isDefault = $isDefault ;
        $this->translations = new ArrayCollection();
    }

    public static function create(
        string $id,
        string $canonicalName,
        ?string $escoUri =null,
        ?string $onetCode = null,
        bool $isDefault = true,
    ){
        return new self(
            id: $id,
            canonicalName: $canonicalName,
            onetCode: $onetCode,
            escoUri: $escoUri,
            isDefault: $isDefault,
        );
    }

    //------------------------
    //-- GETTERS
    //-----------------------

    public function getId(){
        return $this->id;
    }

    public function getEscoUri(){
        return $this->escoUri;
    }

    public function getOnetCode(){
        return $this->onetCode;
    }


    public function isDefault()
    {
        return $this->isDefault;    
    }

    public function getCanonicalName(){
        return $this->canonicalName;
    }

    /**
     * @return array<int, SkillTranslationEntity>
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    
    //----------------------
    //-- SETTER
    //--------------------------



    public function setEscoUri(string $esco_uri)
    {
        $this->escoUri = $esco_uri;
        return $this;
    }


    public function setOnetCode(string $onetCode)
    {
        $this->onetCode = $onetCode;
        return $this;
    }
    

    public function setIsDefault(bool $isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function  setCanonicalName(string $canonicalName)
    {
        $this->canonicalName = $canonicalName;
        return $this;
    }

    public function addTranslation(SkillTranslationEntity $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            if(!$translation->getSkill()){
                $translation->setSkill($this); 
            }
        }

        return $this;
    }
}