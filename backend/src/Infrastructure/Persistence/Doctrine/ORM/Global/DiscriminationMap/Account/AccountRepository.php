<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account;

use App\Domain\Shared\EmailAddress;
use App\Domain\Sharedp\KnownIdentity;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Account\AccountRepositoryInterface;

use Doctrine\ORM\EntityManagerInterface;
use Override;

class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ){}


    #[Override]
    public function exists(?string $uuid = null, ?string $email = null): bool
    {
        if (!$uuid && !$email) {
            return false;
        }

        $criteria = [];
        if ($uuid)  $criteria["id"] = $uuid;
        if ($email) $criteria["email"] = $email;

        $account = $this->em
            ->getRepository(AccountEntity::class)
            ->findOneBy($criteria);

        return $account !== null;
    }

    public function assertExist(?string $uuid = null, ?string $email = null): KnownIdentity
    {
        if (!$uuid && !$email) {
            throw new RessourceNotFound();
        }

        $criteria = [];
        if ($uuid)  $criteria["id"] = $uuid;
        if ($email) $criteria["email"] = $email;

        $account = $this->em
            ->getRepository(AccountEntity::class)
            ->findOneBy($criteria);

        if (!$account) {
            throw new RessourceNotFound();
        }

        return new KnownIdentity(
            uuid: $account->getId(),
            email: $account->getEmail(),
            password: $account->getPassword(),
            role: $account->getRole()    
        );
    }

    
    public function findAll(?int $skip = null, ?int $limit = null): array
    {
        $accounts = $this->em->getRepository(AccountEntity::class)->findBy([], limit: $limit, offset: $skip);
        return array_map(
            fn(AccountEntity $account) => [
                'id' => $account->getId(),
                'email' => $account->getEmail(),
            ],
            $accounts
        );
    }


    public function changeEmail(string $old, string $new): void
    {
        $account = $this->em
                        ->getRepository(AccountEntity::class)
                        ->findOneBy([ "email" => $old ]);
        if(!$account)
            throw new RessourceNotFound();
        $account->setEmail($new);

        $this->em->persist($account);
        $this->em->flush();
    }


    public function changePassword(string $email, string $hash): void
    {
        $account = $this->em
                        ->getRepository(AccountEntity::class)
                        ->findOneBy([ "email" => $email ]);
        if(!$account)
            throw new RessourceNotFound();
        $account->setPassword($hash);

        $this->em->persist($account);
        $this->em->flush();
    }


}