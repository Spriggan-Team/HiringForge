<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account;


use App\Domain\Shared\KnownIdentity;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\AccountLightModel;
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
    public function getAccountLightModel(
        string $uuid
    ): ?AccountLightModel {
        $data = $this->em
            ->createQueryBuilder()
            ->select(
                'a.id AS id',
                'a.firstName AS firstName',
                'a.lastName AS lastName',
                'a.email AS email',
                'IDENTITY(a.image) AS imageId'
            )
            ->from(AccountEntity::class, 'a')
            ->where('a.id = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();

        if ($data === null) {
            return null;
        }

        return new AccountLightModel(
            id: $data['id'],
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'],
            imageId: $data['imageId'],
        );
    }
    

    #[Override]
    public function getProfileImage(string $id): ?StaticMedia
    {
        /** @var AccountEntity|null $account */
        $account = $this->em->find(AccountEntity::class, $id);

        if (!$account) {
            throw new ResourceNotFoundException(
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
    public function changeProfileImage(
        string $accountId,
        StaticMedia $image,
    ): void {
        /** @var AccountEntity|null $account */
        $account = $this->em
            ->getRepository(AccountEntity::class)
            ->find($accountId);

        if ($account === null) {
            throw new ResourceNotFoundException(
                'Account not found when modifying image.'
            );
        }

        $file = FileEntityMapper::toFileEntity($image);

        $account->setImage($file);
        $this->em->flush();
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
            throw new ResourceNotFoundException();
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
            throw new ResourceNotFoundException();
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
            throw new ResourceNotFoundException();
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
            throw new ResourceNotFoundException();
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