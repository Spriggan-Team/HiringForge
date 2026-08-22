<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Security\VerificationTokenEntity;
use Doctrine\ORM\Mapping as ORM;


/**
 * A specialise token identity
 */
#[ORM\Entity]
#[ORM\Table(name: 'otp_verification')]
class OTPVerificationTokenEntity extends VerificationTokenEntity
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'integer')]
    private int $attempts = 0;

    //--------------------
    //---- Relations
    //--------------------

    #[ORM\ManyToOne(
        inversedBy: "otpTokens",
        targetEntity: AccountEntity::class,
    )]
    private ?AccountEntity $account = null;


    //------------------
    //----Creating
    //-------------------

    public function __construct(?AccountEntity $account = null,  ?string $email = null)
    {
        parent::__construct();
        $this->account = $account;
        if ($email) {
            $this->email = $email;
        }
    }

    //---------------
    //---------GETTERS
    //----------------

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getRelatedAccount(): AccountEntity
    {
        return $this->account;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    //------------------------
    //-------Setter
    //----------------------

    public function incrementAttemps(): static
    {
        $this->attempts++;
        return $this;
    }


    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function setAccount(AccountEntity $account): self{
        $this->account = $account;
        return $this;
    }

    public function setAttempts(int $attempts): self{
        $this->attempts = $attempts;
        return $this;
    }
}