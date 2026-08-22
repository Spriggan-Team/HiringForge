<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill;

use Doctrine\ORM\Mapping as ORM;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;


#[ORM\Entity]
#[ORM\Table(name: "skills_translation")]
#[ORM\UniqueConstraint(name: 'unique_slug_per_language', columns: ['slug', 'language_id'])]
class SkillTranslationEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name;   
    
    #[ORM\Column(length: 120)]
    private string $slug;


    //------------------
    //---- RELATIONS
    //-------------------
    
    #[ORM\ManyToOne(
        targetEntity: SkillEntity::class,
        inversedBy: 'translations' 
    )]
    #[ORM\JoinColumn(
        name: "skill_id",
        nullable: false,
        referencedColumnName: "id",
        onDelete: "CASCADE",
    )]
    private SkillEntity $skill;


    #[ORM\ManyToOne(targetEntity: LanguageEntity::class)]
    #[ORM\JoinColumn(
        name: "language_id",
        nullable: false,
        referencedColumnName: "id"
    )]
    private LanguageEntity $language;

    //----------------------
    //--- Construct 
    //----------------------
    
    public function __construct(
        string $name,
        string $slug,
        SkillEntity $skill,
        LanguageEntity $language
    )
    {
        $this->slug = $slug;
        $this->name = $name;
        $this->skill = $skill;
        $this->language = $language;
    }

    public static function create(
        string $name,
        string $slug,
        SkillEntity $skill,
        LanguageEntity $language
    )
    {
        return new self(
            name: $name,
            slug: $slug,
            skill: $skill,
            language: $language
        );
    }

    //-----------------------
    //----- GETTERS
    //----------------------

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSlug(){
        return $this->slug;
    }

    public function getSkill()
    {
        return $this->skill;
    }

    public function getLanguage(){
        return $this->language;
    }

    //------------------
    //--- SETTERS
    //--------------------


    public function setName(string $name){
        $this->name = $name;
        return $this;
    }

    public function setSlug(string $slug){
        $this->slug = $slug;
        return $this;
    }

    public function setSkill(SkillEntity $skill)
    {
        $this->skill = $skill;
        return $this;
    }

    public function setLanguage(LanguageEntity $language)
    {
        $this->language = $language;
        return $this;
    }
}