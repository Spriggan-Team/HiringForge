<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security;

use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table('verification_token')]
class VerificationTokenEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $code_hash = null;

    #[ORM\Column(type: 'integer')]
    private int $attemps = 0;

    #[ORM\Column(enumType: AccountFlowPurpose::class, nullable: false)]
    private ?AccountFlowPurpose $purpose = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    //------------------
    //----Relations
    //-----------------

    #[ORM\ManyToOne(
        targetEntity: AccountEntity::class,
        inversedBy: 'verificationTokens'
    )]
    private AccountEntity $account;

    //---------
    //----Object Creating ..
    //-----------

    public function __construct(AccountEntity $account)
    {
        $this->account = $account;
        $this->createdAt = new \DateTimeImmutable();
    }

    //-----------------------
    //-------GETTERS
    //------------------------

    public function getId(): string
    {
        return $this->id;
    }

    public function getCodeHash()
    {
        return $this->code_hash;
    }

    public function getAttemps(): int
    {
        return $this->attemps;
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

    public function getRelatedAccount(): AccountEntity
    {
        return $this->account;
    }

    //-----------------
    //-------SETTERS--
    //------------------

    public function setId(string $id):static
    {
        $this->id = $id;
        return $this;
    }

    public function setCodeHash(string $code_hash):static
    {
        if(!$this->code_hash){
            $this->code_hash = $code_hash;
        }
        return $this;
    }

    public function incrementAttemps(): static
    {
        $this->attemps++;
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

}