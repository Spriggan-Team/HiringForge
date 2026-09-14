<?php


namespace App\Tests\Unit\Application\JobOffer;

use App\Application\DTO\JobOffer\CreateJobOfferRequest;
use App\Application\Usecases\JobOffer\JobOfferRecorder;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Department\DepartmentRepositoryInterface;

use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferExpertise;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Domain\JobOffer\JobWorkMode;

use App\Domain\Shared\Contract\ContractTypeRepositoryInterface;
use App\Domain\Shared\Language\LanguageRepositoryInterface;
use App\Domain\Shared\Skill\SkillRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


final class JobOfferRecorderTest extends TestCase
{
    /** @var JobOfferRepositoryInterface&MockObject */
    private JobOfferRepositoryInterface&MockObject $jobOfferRepository;

    /** @var CompanyRepositoryInterface&MockObject */
    private CompanyRepositoryInterface&MockObject $companyRepository;

    /** @var CategoryRepositoryInterface&MockObject */
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    /** @var SkillRepositoryInterface&MockObject */
    private SkillRepositoryInterface&MockObject $skillRepository;

    /** @var LanguageRepositoryInterface&MockObject */
    private LanguageRepositoryInterface&MockObject $languageRepository;

    /** @var DepartmentRepositoryInterface&MockObject */
    private DepartmentRepositoryInterface&MockObject $departmentRepository;

    /** @var ContractTypeRepositoryInterface&MockObject */
    private ContractTypeRepositoryInterface&MockObject $contractTypeRepository;

    private JobOfferRecorder $recorder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobOfferRepository = $this->createMock(
            JobOfferRepositoryInterface::class
        );

        $this->companyRepository = $this->createMock(
            CompanyRepositoryInterface::class
        );

        $this->categoryRepository = $this->createMock(
            CategoryRepositoryInterface::class
        );

        $this->skillRepository = $this->createMock(
            SkillRepositoryInterface::class
        );

        $this->languageRepository = $this->createMock(
            LanguageRepositoryInterface::class
        );

        $this->departmentRepository = $this->createMock(
            DepartmentRepositoryInterface::class
        );

        $this->contractTypeRepository = $this->createMock(
            ContractTypeRepositoryInterface::class
        );

        $this->recorder = new JobOfferRecorder(
            repository: $this->jobOfferRepository,
            companyRepository: $this->companyRepository,
            categoryRepository: $this->categoryRepository,
            skillRepository: $this->skillRepository,
            languageRepository: $this->languageRepository,
            departmentRepository: $this->departmentRepository,
            contractTypeRepository: $this->contractTypeRepository,
        );
    }

    public function testItCreatesAndPersistsAJobOffer(): void
    {
        $accountId = 'account-uuid';
        $companyId = 'company-uuid';

        $command = $this->createCommand();

        $this->categoryRepository
            ->expects(self::once())
            ->method('getExistingByIds')
            ->with($command->categories)
            ->willReturn([]);

        $this->companyRepository
            ->expects(self::once())
            ->method('fetchUserCompanyProjection')
            ->with($accountId)
            ->willReturn([
                'id' => $companyId,
            ]);

        $this->skillRepository
            ->expects(self::once())
            ->method('findExistingIds')
            ->with($command->skills)
            ->willReturn($command->skills);

        $this->departmentRepository
            ->expects(self::once())
            ->method('exists')
            ->with($command->departmentId)
            ->willReturn(true);

        $this->contractTypeRepository
            ->expects(self::once())
            ->method('exists')
            ->with($command->contractTypeId)
            ->willReturn(true);

        $this->companyRepository
            ->expects(self::once())
            ->method('isAddressOwnedByUserCompany')
            ->with(
                addressId: $command->location['id'],
                userId: $accountId
            )
            ->willReturn(true);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::isInstanceOf(JobOffer::class),
                $accountId
            );

        $offerId = $this->recorder->execute(
            accountId: $accountId,
            command: $command
        );

        self::assertIsString($offerId);
        self::assertNotSame('', $offerId);
    }

    public function testItThrowsWhenUserHasNoCompany(): void
    {
        $accountId = 'account-uuid';

        $command = $this->createCommand();

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->expects(self::once())
            ->method('fetchUserCompanyProjection')
            ->with($accountId)
            ->willReturn([
                'id' => null,
            ]);

        $this->jobOfferRepository
            ->expects(self::never())
            ->method('save');

        $this->expectException(\DomainException::class);


        $this->recorder->execute(
            accountId: $accountId,
            command: $command
        );
    }

    public function testItAddsSkillsWhenAllSkillsExist(): void
    {
        $command = $this->createCommand();

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->method('fetchUserCompanyProjection')
            ->willReturn([
                'id' => 'company-uuid',
            ]);

        $this->skillRepository
            ->expects(self::once())
            ->method('findExistingIds')
            ->with($command->skills)
            ->willReturn($command->skills);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    function (JobOffer $offer): bool {
                        self::assertSame(
                            [
                                'skill-1',
                                'skill-2',
                            ],
                            $offer->skillsId()
                        );

                        return true;
                    }
                ),
                'account-uuid'
            );

        $this->recorder->execute(
            accountId: 'account-uuid',
            command: $command
        );
    }

    public function testItDoesNotAddSkillsWhenOneSkillDoesNotExist(): void
    {
        $command = $this->createCommand();

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->method('fetchUserCompanyProjection')
            ->willReturn([
                'id' => 'company-uuid',
            ]);

        $this->skillRepository
            ->expects(self::once())
            ->method('findExistingIds')
            ->with($command->skills)
            ->willReturn([
                'skill-1',
            ]);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    function (JobOffer $offer): bool {
                        self::assertSame(
                            [],
                            $offer->skillsId()
                        );

                        return true;
                    }
                ),
                'account-uuid'
            );

        $this->recorder->execute(
            accountId: 'account-uuid',
            command: $command
        );
    }


    public function testItSetsDepartmentWhenDepartmentExists(): void
    {
        $command = $this->createCommand();

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->method('fetchUserCompanyProjection')
            ->willReturn([
                'id' => 'company-uuid',
            ]);

        $this->skillRepository
            ->method('findExistingIds')
            ->willReturn([]);

        $this->departmentRepository
            ->expects(self::once())
            ->method('exists')
            ->with($command->departmentId)
            ->willReturn(true);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    function (JobOffer $offer) use ($command): bool {
                        self::assertSame(
                            $command->departmentId,
                            $offer->departmentId()
                        );

                        return true;
                    }
                ),
                'account-uuid'
            );

        $this->recorder->execute(
            accountId: 'account-uuid',
            command: $command
        );
    }


    public function testItDoesNotSetDepartmentWhenDepartmentDoesNotExist(): void
    {
        $command = $this->createCommand();

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->method('fetchUserCompanyProjection')
            ->willReturn([
                'id' => 'company-uuid',
            ]);

        $this->skillRepository
            ->method('findExistingIds')
            ->willReturn([]);

        $this->departmentRepository
            ->expects(self::once())
            ->method('exists')
            ->with($command->departmentId)
            ->willReturn(false);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    fn (JobOffer $offer): bool =>
                        $offer->departmentId() === null
                ),
                'account-uuid'
            );

        $this->recorder->execute(
            accountId: 'account-uuid',
            command: $command
        );
    }

    public function testItSetsLocationWhenAddressBelongsToCompany(): void
    {
        $command = $this->createCommand();

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->method('fetchUserCompanyProjection')
            ->willReturn([
                'id' => 'company-uuid',
            ]);

        $this->skillRepository
            ->method('findExistingIds')
            ->willReturn([]);

        $this->companyRepository
            ->expects(self::once())
            ->method('isAddressOwnedByUserCompany')
            ->with(
                addressId: $command->location['id'],
                userId: 'account-uuid'
            )
            ->willReturn(true);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    function (JobOffer $offer) use ($command): bool {
                        self::assertSame(
                            $command->location['id'],
                            $offer->locationId()
                        );

                        return true;
                    }
                ),
                'account-uuid'
            );

        $this->recorder->execute(
            accountId: 'account-uuid',
            command: $command
        );
    }


    public function testItDoesNotSetLocationWhenAddressDoesNotBelongToCompany(): void
    {
        $command = $this->createCommand();

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->method('fetchUserCompanyProjection')
            ->willReturn([
                'id' => 'company-uuid',
            ]);

        $this->skillRepository
            ->method('findExistingIds')
            ->willReturn([]);

        $this->companyRepository
            ->expects(self::once())
            ->method('isAddressOwnedByUserCompany')
            ->with(
                addressId: $command->location['id'],
                userId: 'account-uuid'
            )
            ->willReturn(false);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    fn (JobOffer $offer): bool =>
                        $offer->locationId() === null
                ),
                'account-uuid'
            );

        $this->recorder->execute(
            accountId: 'account-uuid',
            command: $command
        );
    }


    public function testItPublishesOfferWhenPublicationStatusIsPublished(): void
    {
        $command = $this->createCommand(
            publicationStatus: JobPublicationStatus::PUBLISHED->value
        );

        $this->categoryRepository
            ->method('getExistingByIds')
            ->willReturn([]);

        $this->companyRepository
            ->method('fetchUserCompanyProjection')
            ->willReturn([
                'id' => 'company-uuid',
            ]);

        $this->skillRepository
            ->method('findExistingIds')
            ->willReturn([]);

        $this->jobOfferRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    function (JobOffer $offer): bool {
                        self::assertSame(
                            JobPublicationStatus::PUBLISHED,
                            $offer->publicationStatus()
                        );

                        return true;
                    }
                ),
                'account-uuid'
            );

        $this->recorder->execute(
            accountId: 'account-uuid',
            command: $command
        );
    }


    private function createCommand(
        ?string $publicationStatus = null
    ): CreateJobOfferRequest {
        return new CreateJobOfferRequest(
            title: 'Senior PHP Developer',
            content: [
                'property' => 'Job description',
            ],
            categories: [],
            skills: [
                'skill-1',
                'skill-2',
            ],

            languages: [],
            departmentId: 10,

            workMode: JobWorkMode::REMOTE->value,
            expertise: JobOfferExpertise::SENIOR->value,
            contractTypeId: null,

            salary: null,
            visibilityStatus: JobOfferVisibilityStatus::PUBLIC->value,

            publicationDate: null,
            location: [
                'id' => 42,
            ],

            publicationStatus: $publicationStatus,
        );
    }
}
