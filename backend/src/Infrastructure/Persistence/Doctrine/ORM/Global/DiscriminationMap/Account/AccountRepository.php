<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account;


use App\Domain\Shared\KnownIdentity;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\Account\AccountRole;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper\FileEntityMapper;
use Doctrine\ORM\EntityManagerInterface;
use Override;

class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ){}

    
    #[Override]
    public function getProfileImage(string $id): ?StaticMedia
    {
        /** @var AccountEntity|null $account */
        $account = $this->em->find(AccountEntity::class, $id);

        if (!$account) {
            throw new RessourceNotFound(
                sprintf('No account found with id "%s".', $id)
            );
        }

        $image = $account->getImage();

        if (!$image) {
            return null;
        }

        return FileEntityMapper::toStaticDomainMedia($image);
    }


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

        $qb = $this->em->createQueryBuilder()
            ->select('partial a.{id, email, password}') //-- ignore legacy
            ->from(AccountEntity::class, 'a');

        if ($uuid) {
            $qb->andWhere('a.id = :id')->setParameter('id', $uuid);
        }
        if ($email) {
            $qb->andWhere('a.email = :email')->setParameter('email', $email);
        }

        $accountData = $qb->getQuery()->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        if (!$accountData) {
            throw new RessourceNotFound();
        }


        return new KnownIdentity(
            uuid: $accountData['id'],
            email: $accountData['email'],
            password: $accountData['password'],
            accountType: AccountRole::tryFrom($accountData['account_role']) ?? AccountRole::UNKNOWN
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


    
    #[Override]
    public function fetchView(string $id)
    {
        throw new \Exception('Not implemented');
    }
}