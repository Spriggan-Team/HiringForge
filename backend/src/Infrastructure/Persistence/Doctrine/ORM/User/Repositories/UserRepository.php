<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Repositories;

use App\Api\Responder\ApiResponse;
use App\Domain\Exception\ResourceCreationRejected;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\User\User as DomainEntity;
use App\Domain\Shared\KnownIdentity;

use App\Domain\Shared\Account\AccountRole;
use App\Domain\User\UserLightModel;
use App\Domain\User\UserRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Override;



class UserRepository implements UserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em){}

    /**
     * Retrieve information about recruiter.
     *
     * @return array{
     *     firstName: string,
     *     lastName: string,
     *     description: ?string,
     *     email: string,
     *     image: ?array{
     *         id: int,
     *         name: string,
     *         mime: string,      
     *     }
     * }
     */
    #[Override]
    public function getRecruiterView(string $userId): array
    {
        $result = $this->em->createQueryBuilder()
            ->select(
                'u.lastName AS lastName',
                'u.firstName AS firstName',
                'u.description AS description',
                'u.email AS email',
                'i.id AS imageId',
                'i.name AS imageName',
                'i.mime AS imageMime'
            )
            ->from(UserEntity::class, 'u')
            ->leftJoin('u.image', 'i')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            throw new \RuntimeException('Recruiter not found.');
        }

        return [
            'firstName' => $result['firstName'],
            'lastName' => $result['lastName'],
            'description' => $result['description'],
            'email' => $result['email'],
            
            'image' => $result['imageId'] ? [
                'id' => $result['imageId'],
                'name' => $result['imageName'],
                'mime' => $result['imageMime'],
            ] : null,
        ];
    }



    #[Override]
    public function exists(string $userId): bool
    {
        $result = $this->em->createQueryBuilder()
            ->select('1')
            ->from(UserEntity::class, 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }

    #[Override]
    public function getLightModelById(string $userId): ?array
    {
        return $this->em->createQueryBuilder()
            ->select('u.lastName, u.firstName')
            ->from(UserEntity::class, 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    #[Override]
    public function assertExist(?string $uuid = null, ?string $email = null): KnownIdentity
    {
        $criteria = [];
        if($uuid)
            $criteria["id"] = $uuid;
        else if($email)
            $criteria["email"] = $email;

        $accountRepository = $this->em->getRepository(AccountEntity::class);
        $userRepository = $this->em->getRepository(UserEntity::class);

        $account = $accountRepository->findOneBy($criteria);
        if(!$account)
            throw new ResourceNotFoundException("User Account not found");

        $user = $userRepository->find($account->getId());
        if(!$user)
            throw new ResourceNotFoundException("User account not identified");

        return new KnownIdentity(
            uuid: $user->getId(),
            email: $user->getEmail(),
            password: $user->getPassword(),
            accountType: AccountRole::USER
        );
    }


    public function findById(string $uuid): DomainEntity
    {
        $entity = $this->em->find(UserEntity::class, $uuid);
        // $account = $this->em->find(AccountEntity::class, $uuid);
        // ApiResponse::$logger->error("user Account entity: " . json_encode($account));
        // ApiResponse::$logger->error("Account id: " . json_encode($uuid));
        if(!$entity){
            throw new ResourceNotFoundException("[USER] This id is not registered");
        }
        return UserEntityMapper::toDomainEntity($entity);
    }

    

    public function findByEmail(string $email): DomainEntity
    {
        $entity = $this->em->getRepository(UserEntity::class)->findOneBy([
            "email" => $email
        ]);
        if(!$entity){
            throw new ResourceNotFoundException("[USER] This email belogns to no user");
        }
        return UserEntityMapper::toDomainEntity($entity);
    }

    /**
     * @throws ResourceCreationRejected
     * @return void
     */
    public function save(DomainEntity $user): void
    {
        try{
            $company = $this->em->getReference(CompanyEntity::class, $user->companyId());
            if(!$user->id()){
                $entity = UserEntityMapper::toDoctrineEntity($user, $company);
                $this->em->persist($entity);
            }
            else{
                $entity = $this->em->find(UserEntity::class, $user->id());
                UserEntityMapper::copy($user, $entity);
            }

            $this->em->flush();
        }
        catch(\Throwable  $exception){
            // throw new ResourceCreationRejected(previous: $exception);
            throw $exception;
        }
    }

    

    public function delete(string $uuid): void
    {
        $entity = $this->em->find(UserEntity::class, $uuid);
        if(!$entity){
            throw new ResourceNotFoundException("This ressource does not exist");
        }
        $this->em->remove($entity);
        $this->em->flush();
    }



    public function change(DomainEntity $user, string $uuid, ?array $deleteImages=null): void
    {
        throw new \Exception('Not implemented');
    }


    #[Override]
    public function getOrganizationId(string $userId): string
    {
        $user = $this->em->find(UserEntity::class, $userId);
        if (!$user) {
            throw new ResourceNotFoundException("User not found");
        }

        $company = $user->getCompany();
        return $company->getId(); 
    }

}
