<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'blacklisted_token')]
#[ORM\Index(columns: ['jti'], name: 'idx_blacklisted_jti')] // Index - performances !
class BlacklistedTokenEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?\Ramsey\Uuid\Uuid $id = null;

    // JTI (l'UUID unique du JWT)
    #[ORM\Column(type: 'string', length: 36, unique: true, nullable: false)]
    private string $jti;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $blacklistedAt;

    #[ORM\Column(length: 50, nullable: false)]
    private string $reason;


    public function __construct(
        string $jti, 
        \DateTimeImmutable $expiresAt, 
        string $reason = 'logout'
    ) {
        $this->jti = $jti;
        $this->reason = $reason;
        $this->expiresAt = $expiresAt;
        $this->blacklistedAt = new \DateTimeImmutable();
    }


    //-- GETTERS

    public function getId(){
        return $this->id;
    }

    public function getJti(){
        return $this->jti;
    }
    
    public function getReason(): string
    {
        return $this->reason;
    }

    public function getBlacklistedAt(): \DateTimeImmutable
    {
        return $this->blacklistedAt;
    }

    public function getExpiredAt(){
        return $this->expiresAt;
    }
}