<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Company;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity]
#[ORM\Table(
    name: 'company_address',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_company_address', columns: ['company_id', 'address_id'])
    ]
)]
class CompanyAddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    //-------------------------
    //  Relation
    //------------------------
    
    #[ORM\ManyToOne(
        targetEntity: CompanyEntity::class,
        inversedBy: 'companyAddresses'
    )]
    #[ORM\JoinColumn(name: 'company_id', nullable: false, onDelete: 'CASCADE')]
    private CompanyEntity $company;

    #[ORM\ManyToOne(
        targetEntity: AddressEntity::class,
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(name: 'address_id', nullable: false)]
    private AddressEntity $address;

    
    public function __construct(CompanyEntity $company, AddressEntity $address){
        $this->company = $company;
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


    public function getUser(): CompanyEntity
    {
        return $this->company;
    }

    //------------------------------
    //      SETTERS
    //----------------------------

    public function attachToUser(CompanyEntity $company): static
    {
        $this->company = $company;
        return $this;
    }


    public function addAddress(AddressEntity $address): static 
    {
        $this->address = $address;
        return $this;    
    }
}