<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;


/**
 * Cette class a été crée pour permettre a un type de compte d'en superviser plusieurs autres
 */
#[ORM\Entity]
#[ORM\Table('account_supervision')]
class AdminSupervisionEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    private string $id;


    #[ORM\ManyToMany(targetEntity: AccountEntity::class)]
    #[ORM\JoinTable(name: 'admin_supervised_users')]            //--Join table
    private Collection $supervisedUsers;


    public function __construct()
    {
        $this->supervisedUsers = new ArrayCollection();
    }



    public function removeSupervised(AccountEntity $user): void
    {
        $this->supervisedUsers->removeElement($user);
    }
}
