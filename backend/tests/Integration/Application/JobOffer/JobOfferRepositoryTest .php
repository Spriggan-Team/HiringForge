<?php


declare(strict_types=1);

namespace App\Tests\Integration\Application\JobOffer;

use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\JobOffer\JobPublicationStatus;

use App\Tests\Support\Factory\Company\CompanyEntityFactory;
use App\Tests\Support\Factory\JobOffer\JobOfferEntityFactory;
use App\Tests\Support\Factory\User\UserEntityFactory;

use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class JobOfferRepositoryTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private JobOfferRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->repository = self::getContainer()->get(
            JobOfferRepositoryInterface::class
        );
    }

    public function testItCanCheckIfJobOfferExists(): void
    {
        $jobOffer = JobOfferEntityFactory::createOne();

        self::assertTrue(
            $this->repository->exists(
                $jobOffer->getId()
            )
        );
    }

    public function testItReturnsFalseWhenJobOfferDoesNotExist(): void
    {
        self::assertFalse(
            $this->repository->exists(
                '00000000-0000-0000-0000-000000000000'
            )
        );
    }

    public function testItCanFindJobOfferByIdForOwner(): void
    {
        $user = UserEntityFactory::createOne();

        $jobOffer = JobOfferEntityFactory::createOne([
            'user' => $user,
        ]);

        $result = $this->repository->findById(
            $user->getId(),
            $jobOffer->getId()
        );

        self::assertInstanceOf(
            JobOffer::class,
            $result
        );

        self::assertSame(
            $jobOffer->getId(),
            $result->id()
        );
    }

    public function testItRejectsJobOfferFromAnotherUser(): void
    {
        $owner = UserEntityFactory::createOne();
        $otherUser = UserEntityFactory::createOne();

        $jobOffer = JobOfferEntityFactory::createOne([
            'author' => $owner,
        ]);

        $this->expectException(\Throwable::class);

        $this->repository->assertRelationWithUser(
            accountId: $otherUser->getId(),
            offerId: $jobOffer->getId()
        );
    }

    public function testItCanSaveJobOffer(): void
    {
        $user = UserEntityFactory::createOne();
        $company = CompanyEntityFactory::createOne([
            'owner' => $user,
        ]);

        $offer = JobOffer::create(
            id: \Symfony\Component\Uid\Uuid::v4()->toRfc4122(),
            title: 'PHP Developer',
            companyId: $company->getId(),
            content: ['desc' =>'Backend development'],
            categories: [],
        );

        $this->repository->save(
            offer: $offer,
            userId: $user->getId()
        );

        self::assertTrue(
            $this->repository->exists($offer->id())
        );
    }

    public function testItCanDeleteJobOffer(): void
    {
        $user = UserEntityFactory::createOne();

        $jobOffer = JobOfferEntityFactory::createOne([
            'author' => $user,
        ]);

        $id = $jobOffer->getId();

        $this->repository->delete(
            uuid: $id,
            accountId: $user->getId()
        );

        self::assertFalse(
            $this->repository->exists($id)
        );
    }

    public function testItCanUpdatePublicationStatusDirectly(): void
    {
        $user = UserEntityFactory::createOne();

        $jobOffer = JobOfferEntityFactory::createOne([
            'author' => $user,
            'publicationStatus' => JobPublicationStatus::DRAFT,
        ]);

        $this->repository->updatePublicationStatusDirectly(
            jobId: $jobOffer->getId(),
            prevStatus: JobPublicationStatus::DRAFT,
            newStatus: JobPublicationStatus::PUBLISHED
        );

        $this->repository->findById(
            $user->getId(),
            $jobOffer->getId()
        );


        self::assertSame(
            JobPublicationStatus::PUBLISHED,
            $jobOffer->getPublicationStatus()
        );
    }
}