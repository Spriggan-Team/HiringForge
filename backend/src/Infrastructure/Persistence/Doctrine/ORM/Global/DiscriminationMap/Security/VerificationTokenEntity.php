<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Security;

use App\Domain\Shared\Account\AccountFlowPurpose;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('verification_token')]
#[ORM\InheritanceType('JOINED')]
class VerificationTokenEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;


    #[ORM\Column(length: 255, nullable: false)]
    private ?string $code_hash = null;


    #[ORM\Column(enumType: AccountFlowPurpose::class, nullable: false)]
    private ?AccountFlowPurpose $purpose = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $expiresAt = null; //tell when token as been mark as blaclisted

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;


    //---------
    //----Object Creating ..
    //-----------

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function create(
        ?string $id = null, 
        ?string $code_hash = null,
        ?AccountFlowPurpose $purpose = null,
        ?\DateTimeImmutable $expiresAt = null,
    ): self {
        $verificationToken = new self();
        $verificationToken->setId(new Uuid($id))
                           ->setCodeHash($code_hash)
                           ->setPurpose($purpose)
                           ->setExpiresAt($expiresAt);
        return $verificationToken;
    }

    //-----------------------
    //-------GETTERS
    //------------------------
    
    public function getId(): string
    {
        return $this->id?->toRfc4122();
    }

    public function getCodeHash()
    {
        return $this->code_hash;
    }


    public function getPurpose(): AccountFlowPurpose
    {
        return $this->purpose;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }


    //-----------------
    //-------SETTERS--
    //------------------

    public function setId(Uuid $id):static
    {
        $this->id = $id;
        return $this;
    }

    public function setCodeHash(string $code_hash):static
    {
        $this->code_hash = $code_hash;
        return $this;
    }

    
    public function setPurpose(AccountFlowPurpose $purpose): static
    {
        if(!$this->purpose){
            $this->purpose = $purpose;
        }
        return $this;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt):static
    {
        if(!$this->expiresAt){
            $this->expiresAt = $expiresAt;
        }
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt){
        $this->createdAt =$createdAt;
        return $this;
    }

}