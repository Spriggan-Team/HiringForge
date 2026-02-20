<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

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

    #[ORM\OneToOne(
        inversedBy: "adminSupervision"
    )]
    #[ORM\JoinColumn(nullable: false)]
    private AccountEntity $account;

    #[ORM\ManyToMany(targetEntity: AccountEntity::class)]
    #[ORM\JoinTable(name: 'admin_supervised_users')]            //Join table
    private Collection $supervisedUsers;

    public function __construct(AccountEntity $account)
    {
        $this->account = $account;
        $this->supervisedUsers = new ArrayCollection();
    }

    public function supervise(AccountEntity $user): void
    {
        if ($user->isAdmin()) {
            throw new \DomainException("An admin cannot supervise another admin.");
        }
        if(!$this->supervisedUsers->contains($user)){
            $this->supervisedUsers->add($user);
        }

    }

    public function removeSupervised(AccountEntity $user): void
    {
        $this->supervisedUsers->removeElement($user);
    }
}
