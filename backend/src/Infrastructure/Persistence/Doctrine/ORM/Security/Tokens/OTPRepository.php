<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens;

use App\Domain\Exception\RessourceNotFound;
use App\Domain\OTP\OTP;
use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;

use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens\Mapper\OTPVerificationEntityMapper;




class OTPRepository implements OTPRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private OTPVerificationEntityMapper $mapper,
    ) {}


    public function getLastVerificationTokenWithPurpose(
        string $email,
        AccountFlowPurpose $purpose
    ): OTP
    {
        $entity = $this->em
            ->getRepository(OTPVerificationTokenEntity::class)
            ->createQueryBuilder('otp')
            ->where('otp.email = :email')
            ->andWhere('otp.purpose = :purpose')
            ->setParameter('email', $email)
            ->setParameter('purpose', $purpose)
            ->orderBy('otp.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity) {
            throw new RessourceNotFound();
        }

        return $this->mapper->toDomainEntity($entity);
    }


    public function save(string $email, OTP $otp): void
    {
        $account = $this->em
            ->getRepository(AccountEntity::class)
            ->findOneBy(['email' => $email]);

        $entity = new OTPVerificationTokenEntity();

        $entity
            ->setEmail($email)
            ->setCodeHash($otp->getHashCode())
            ->setPurpose($otp->getPurpose())
            ->setExpiresAt($otp->getExpiresAt());

        //-- account (optionnal)
        if ($account) {
            $entity->setAccount($account);
        }

        $this->em->persist($entity);
        $this->em->flush();
    }

}