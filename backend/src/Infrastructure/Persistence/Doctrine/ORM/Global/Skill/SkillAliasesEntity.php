<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "skill_aliases")]
class SkillAliasesEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private ?int $id = null;
    
    #[ORM\Column(length: 100, unique: true)]
    private string $alias;
    

    //----------------
    //- RELATIONS
    //-----------------
     #[ORM\ManyToOne(targetEntity: SkillEntity::class)]
    #[ORM\JoinColumn(
        name: "skill_id",
        referencedColumnName: "id",
        nullable: false,
        onDelete: "CASCADE"
    )]
    private SkillEntity $skill;
    
    //------------------------
    //-----CONSTRUCTOR/BUILDING
    //-------------------------

    public function __construct(
        string $alias,
        ?int $id = null,
    ){
        $this->id = $id;
        $this->alias = $alias;
    }

    public static function create(
        string $alias,
        ?int $id = null,
    ){
        return new self(
            id: $id,
            alias: $alias
        );
    }

    //---------------------------
    //--- GETTERS
    //---------------------------

    public function getId()
    {
        return $this->id;
    }

    public function getAlias(){
        return $this->alias;
    }

    public function getSkill(){
        return $this->skill;
    }

    //-------------------
    //------SETTERS
    //---------------------

    public function setAlias(string $alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function setSkill(SkillEntity $skill){
        $this->skill = $skill;
        return $this;
    }

}
