<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use Doctrine\ORM\Mapping as ORM;


/**
 * This entity class is used to construct a one-to-one relationship between User and Address;
 * It may in the future build a many to many relationship between user and address (then this table will represent the user's sites )
 */
#[ORM\Entity]
#[ORM\Table(
    name: 'user_address',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_user_address', columns: ['user_id', 'address_id'])
    ]
)]
class UserAddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id= null;

    //-------------------------
    //  Relations
    //------------------------
    
    #[ORM\OneToOne(
        targetEntity: UserEntity::class,
        inversedBy: 'userAddress'
    )]
    #[ORM\JoinColumn(nullable: false)]
    private UserEntity $user;


    #[ORM\OneToOne(
        targetEntity: AddressEntity::class,
        inversedBy: 'address',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(nullable: false)]
    private AddressEntity $address;

    public function __construct(UserEntity $user, AddressEntity $address){
        $this->user = $user;
        $this->address = $address;
    }

    //----------------------------
    //---------GETTERS
    //---------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdrdress(): AddressEntity
    {
        return $this->address;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    //------------------------------
    //      SETTERS
    //----------------------------

    public function attachToUser(UserEntity $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function setAddress(AddressEntity $address) : static 
    {
        $this->address = $address;
        return $this;    
    }
}