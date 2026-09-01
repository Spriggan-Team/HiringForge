<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens;

use App\Domain\OTP\OTP;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;

use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens\Mapper\OTPVerificationEntityMapper;
use Override;

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
            throw new ResourceNotFoundException();
        }

        return $this->mapper->toDomainEntity($entity);
    }


    public function save(string $email, OTP $otp): void
    {
        $account = $this->em
            ->getRepository(AccountEntity::class)
            ->findOneBy(['email' => $email]);
        
        //-- Control purpose validation
        if($otp->getPurpose() !== AccountFlowPurpose::SIGN_UP  && !$account)
            throw new ResourceNotFoundException(
                "No associated account detected for this otp code"
            );

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

    
    #[Override]
    public function update(string $email, OTP $otp): void
    {
        $account = $this->em
            ->getRepository(AccountEntity::class)
            ->findOneBy(['email' => $email]);

        if (!$account) {
            throw new ResourceNotFoundException(
                "No associated account detected for this OTP code"
            );
        }

        $entity = $this->em
            ->getRepository(OTPVerificationTokenEntity::class)
            ->findOneBy([
                'account' => $account,
                'purpose' => $otp->purpose->value
            ]);

        if (!$entity) {
            throw new ResourceNotFoundException("The OTP verification token to update does not exist");
        }

        $entity->setAttempts($otp->attempts);
        $this->em->flush();
    }
}