<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use Doctrine\DBAL\Types\Types;
use \Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "resume")]
class CandidateResumeEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hasbeenAnalyzed = false;


    //--------------------------------------
    // Relations
    //---------------------------------------

    #[ORM\ManyToOne(
        targetEntity: CandidateEntity::class,
        inversedBy: "resumes"
    )]
    #[ORM\JoinColumn(nullable: false)]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(
        targetEntity: FileEntity::class,
        cascade: ['persist', 'remove'],
    )]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private FileEntity $file;

    //--------------------
    //---- Constructing
    //----------------------
    
    private function __construct(
        CandidateEntity $candidate,
        FileEntity $file,
        ?string $id = null
    ) {
        $this->candidate = $candidate;
        $this->file = $file;
        $this->id = $id;
    }




    public static function create(CandidateEntity $candidate, FileEntity $file): static
    {
        return new static($candidate, $file);
    }

    public static function reconstitute(string $id, CandidateEntity $candidate, FileEntity $file): static
    {
        return new static($candidate, $file, $id);
    }

    
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCandidate(): CandidateEntity
    {
        return $this->candidate;
    }



    public function setCandidate(CandidateEntity $candidate): static
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getFile(): FileEntity
    {
        return $this->file;
    }

    public function setFile(FileEntity $file): static
    {
        $this->file = $file;

        return $this;
    }

        
    public function hasItbeenAnalyzed(){
        return $this->hasbeenAnalyzed;
    }


    public function markAsAnalyzed(bool $state){
        $this->hasbeenAnalyzed = $state;
        return $this;
    }


}