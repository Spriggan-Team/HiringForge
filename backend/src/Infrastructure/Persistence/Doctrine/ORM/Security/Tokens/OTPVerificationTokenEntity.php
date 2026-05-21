<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Security\VerificationTokenEntity;
use Doctrine\ORM\Mapping as ORM;


/**
 * A specialise token identity
 */
#[ORM\Table()]
#[ORM\Entity('otp_verification')]
class OTPVerificationTokenEntity extends VerificationTokenEntity
{
    #[ORM\Column(type: 'integer')]
    private int $attemps = 0;

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

    public function __construt(AccountEntity $account)
    {
        $this->account = $account;
    }

    //---------------
    //---------GETTERS
    //----------------

    public function getAttemps(): int
    {
        return $this->attemps;
    }

    public function getRelatedAccount(): AccountEntity
    {
        return $this->account;
    }

    //------------------------
    //-------Setter
    //----------------------

    public function incrementAttemps(): static
    {
        $this->attemps++;
        return $this;
    }

}