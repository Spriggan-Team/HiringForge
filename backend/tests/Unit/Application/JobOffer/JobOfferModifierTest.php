<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\JobOffer;

use App\Application\DTO\JobOffer\UpdateJobOfferRequest;
use App\Application\Usecases\JobOffer\JobOfferModifier;
use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferExpertise;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Domain\JobOffer\JobWorkMode;

use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;



final class JobOfferModifierTest extends TestCase
{
    /** @var JobOfferRepositoryInterface&MockObject */
    private JobOfferRepositoryInterface&MockObject $repository;

    private JobOfferModifier $modifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(
            JobOfferRepositoryInterface::class
        );

        $this->modifier = new JobOfferModifier(
            $this->repository
        );
    }

    public function test_itUpdates_jobOffer(): void
    {
        $offerId = 'offer-uuid';

        /*
         * IMPORTANT :
         * JobOffer est final, donc on utilise une vraie instance.
         */
        $offer = $this->createJobOffer($offerId);

        $command = $this->createCommand($offerId);

        $this->repository
            ->expects(self::once())
            ->method('assertRelationWithUser')
            ->with(
                accountId: 'user-uuid',
                offerId: $offerId
            );

        $this->repository
            ->expects(self::once())
            ->method('findById')
            ->with('user-uuid', 'offer-uuid')
            ->willReturn($offer);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($offer);

        $this->modifier->execute(
            userId: 'user-uuid',
            command: $command
        );

        /*
         * On vérifie maintenant l'état réel de l'objet.
         */

        self::assertSame(
            'Updated title',
            $offer->title()
        );

        self::assertSame(
            ['props' => 'Updated content'],
            $offer->content()
        );

        self::assertSame(
            20,
            $offer->locationId()
        );

        self::assertSame(
            30,
            $offer->departmentId()
        );

        self::assertSame(
            1,
            $offer->contractId()
        );

        self::assertSame(
            JobWorkMode::REMOTE,
            $offer->jobWorkMode()
        );

        self::assertSame(
            JobOfferExpertise::SENIOR,
            $offer->expertise()
        );

        self::assertSame(
            ['category-1'],
            $offer->categories()
        );

        self::assertSame(
            ['skill-1', 'skill-2'],
            $offer->skillsId()
        );

        self::assertSame(
            JobOfferVisibilityStatus::PUBLIC,
            $offer->visibilityStatus()
        );

        // self::assertSame(
        //     JobPublicationStatus::PUBLISHED,
        //     $offer->publicationStatus()
        // );


    }

    public function tes_it_verifiesOwnership_vefore_loading_offer(): void
    {
        $offerId = 'offer-uuid';

        /*
         * L'utilisateur n'est pas propriétaire.
         *
         * On configure assertRelationWithUser une seule fois.
         */
        $this->repository
            ->expects(self::once())
            ->method('assertRelationWithUser')
            ->with(
                accountId: 'user-uuid',
                offerId: $offerId
            )
            ->willThrowException(
                new \DomainException('Unauthorized')
            );

        /*
         * L'offre ne doit surtout pas être chargée.
         */
        $this->repository
            ->expects(self::never())
            ->method('findById');

        /*
         * Et elle ne doit pas être sauvegardée.
         */
        $this->repository
            ->expects(self::never())
            ->method('save');

        $command = $this->createMinimalCommand($offerId);

        $this->expectException(\DomainException::class);

        $this->modifier->execute(
            userId: 'user-uuid',
            command: $command
        );
    }

    public function test_it_does_not_updateEmpty_fields(): void
    {
        $offerId = 'offer-uuid';

        /*
         * Offre initiale.
         */
        $offer = $this->createJobOffer($offerId);

        /*
         * On mémorise l'état initial.
         */
        $initialTitle = $offer->title();
        $initialContent = $offer->content();
        $initialLocationId = $offer->locationId();
        $initialDepartmentId = $offer->departmentId();
        $initialContractId = $offer->contractId();
        $initialWorkMode = $offer->jobWorkMode();
        $initialExpertise = $offer->expertise();
        $initialCategories = $offer->categories();
        $initialSkills = $offer->skillsId();
        $initialVisibility = $offer->visibilityStatus();
        $initialPublicationStatus = $offer->publicationStatus();

        /*
         * Tous les champs optionnels sont absents.
         *
         * CORRECTION :
         * content doit être un array, pas null.
         */
        $command = new UpdateJobOfferRequest(
            id: $offerId,
            title: "",
            content: [],
            location: [],
            departmentId: null,
            contractTypeId: null,
            workMode: null,
            expertise: null,
            salary: null,
            categories: [],
            skills: [],
            visibilityStatus: null,
            publicationStatus: null,
            publicationDate: null,
        );

        $this->repository
            ->expects(self::once())
            ->method('assertRelationWithUser')
            ->with(
                accountId: 'user-uuid',
                offerId: $offerId
            );

        $this->repository
            ->expects(self::once())
            ->method('findById')
            ->with(
                'user-uuid',
                'offer-uuid'
            )
            ->willReturn($offer);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($offer);

        $this->modifier->execute(
            userId: 'user-uuid',
            command: $command
        );

        /*
         * Aucun champ ne doit avoir changé.
         */
        self::assertSame(
            $initialTitle,
            $offer->title()
        );

        self::assertSame(
            $initialContent,
            $offer->content()
        );

        self::assertSame(
            $initialLocationId,
            $offer->locationId()
        );

        self::assertSame(
            $initialDepartmentId,
            $offer->departmentId()
        );

        self::assertSame(
            $initialContractId,
            $offer->contractId()
        );

        self::assertSame(
            $initialWorkMode,
            $offer->jobWorkMode()
        );

        self::assertSame(
            $initialExpertise,
            $offer->expertise()
        );

        self::assertSame(
            $initialCategories,
            $offer->categories()
        );

        self::assertSame(
            $initialSkills,
            $offer->skillsId()
        );

        self::assertSame(
            $initialVisibility,
            $offer->visibilityStatus()
        );

        self::assertSame(
            $initialPublicationStatus,
            $offer->publicationStatus()
        );
    }

    public function test_it_updates_publicationStatus(): void
    {
        $offerId = 'offer-uuid';

        /*
         * Offer start as a draft
         * DRAFT -> PUBLISHED is authorized
         */
        $offer = $this->createJobOffer($offerId);

        self::assertSame(
            JobPublicationStatus::DRAFT,
            $offer->publicationStatus()
        );

        $command = $this->createMinimalCommand(
            $offerId,
            JobPublicationStatus::PUBLISHED->value
        );

        $this->repository
            ->expects(self::once())
            ->method('assertRelationWithUser')
            ->with(
                accountId: 'user-uuid',
                offerId: $offerId
            );

        $this->repository
            ->expects(self::once())
            ->method('findById')
            ->with(
                'user-uuid',
                'offer-uuid'
            )
            ->willReturn($offer);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($offer);

        $this->modifier->execute(
            userId: 'user-uuid',
            command: $command
        );

        self::assertSame(
            JobPublicationStatus::PUBLISHED,
            $offer->publicationStatus()
        );
    }


    /**
     * used for main test
     */
    private function createCommand(string $id): UpdateJobOfferRequest
    {
        return new UpdateJobOfferRequest(
            id: $id,
            title: 'Updated title',
            content: [
                'props' => 'Updated content',
            ],

            location: [
                'id' => 20,
            ],

            departmentId: 30,
            contractTypeId: null,

            workMode: JobWorkMode::REMOTE->value,
            expertise: JobOfferExpertise::SENIOR->value,
            salary: null,

            categories: [
                'category-1',
            ],
            skills: [
                'skill-1',
                'skill-2',
            ],

            visibilityStatus: JobOfferVisibilityStatus::PUBLIC->value,
            publicationStatus: JobPublicationStatus::DRAFT->value,
            publicationDate: null,
        );
    }

    /**
     * Commande minimale.
     */
    private function createMinimalCommand(
        string $id,
        ?string $publicationStatus = null
    ): UpdateJobOfferRequest {
        return new UpdateJobOfferRequest(
            id: $id,

            title: "Some title 2",
            content: [],

            location: [],
            departmentId: null,

            contractTypeId: null,

            workMode: null,
            expertise: null,

            salary: null,
            categories: [],

            skills: [],
            visibilityStatus: null,
            publicationStatus: $publicationStatus,
            publicationDate: null,
        );
    }

    /**
     * Création d'une vraie instance JobOffer.
     *
     * JobOffer étant final, aucun mock n'est utilisé.
     */
    private function createJobOffer(string $id): JobOffer
    {
        return JobOffer::hydrate(
            id: $id,

            companyId: 'company-uuid',

            title: 'Original title',
            content: [
                'property' => 'Original content',
            ],

            createdAt: new \DateTimeImmutable('-2 days'),
            updatedAt: new \DateTimeImmutable('-1 day'),

            publicationStatus: JobPublicationStatus::DRAFT,
            visibilityStatus: JobOfferVisibilityStatus::PUBLIC,
            activityStatus: null,

            categories: [
                'original-category',
            ],

            images: [],

            locationId: 10,
            departmentId: 15,
            jobWorkMode: JobWorkMode::ONSITE,

            applications: [],

            skillsId: [
                'original-skill',
            ],

            languages: [],
            expertise: JobOfferExpertise::JUNIOR,
            contractId: 1,
            minSalary: 30000.0,
            maxSalary: 40000.0,
            currency: 'EUR',

            publicationDate: null,
        );
    }
}
