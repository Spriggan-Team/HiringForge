<?php

namespace App\Tests\Unit\Application\Usecases\Application;

use App\Application\Usecases\Application\BulkApplicationStatusChange;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Notification\NotificationRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BulkApplicationStatusChangeTest extends TestCase
{
    private ApplicationRepositoryInterface&MockObject $applicationRepo;
    private NotificationRepositoryInterface&MockObject $notificationRepo;
    private BulkApplicationStatusChange $useCase;

    protected function setUp(): void
    {
        $this->applicationRepo = $this->createMock(ApplicationRepositoryInterface::class);
        $this->notificationRepo = $this->createMock(NotificationRepositoryInterface::class);

        $this->useCase = new BulkApplicationStatusChange(
            $this->applicationRepo,
            $this->notificationRepo
        );
    }

    public function test_does_nothing_if_ids_array_is_empty(): void
    {
        $this->applicationRepo->expects($this->never())->method('assertRecruiterHasAccessToApplicationCollection');
        $this->applicationRepo->expects($this->never())->method('bulkChangeStatus');
        $this->notificationRepo->expects($this->never())->method('saveAll');

        $this->useCase->execute('user-123', [], JobApplicationStatus::SHORTLISTED);
    }

    public function test_successfully_processes_bulk_status_change(): void
    {
        $userId = 'user-123';
        $ids = ['app-1', 'app-2'];

        $this->applicationRepo->expects($this->once())
            ->method('assertRecruiterHasAccessToApplicationCollection')
            ->with($userId, $ids);

        $this->applicationRepo->expects($this->once())
            ->method('getStatusesByIds')
            ->with($ids)
            ->willReturn([
                'app-1' => JobApplicationStatus::APPLIED,
                'app-2' => JobApplicationStatus::APPLIED,
            ]);

        $this->applicationRepo->expects($this->once())
            ->method('bulkChangeStatus')
            ->with($ids, JobApplicationStatus::SHORTLISTED);

        $this->applicationRepo->expects($this->once())
            ->method('getApplicationContext')
            ->with('app-1')
            ->willReturn([
                'applicationId' => 'app-1',
                'candidateId' => 'cand-1',
                'companyId' => 'comp-100',
            ]);

        // Vérifie qu'on enregistre bien un tableau de 2 notifications
        $this->notificationRepo->expects($this->once())
            ->method('saveAll')
            ->with($this->callback(fn(array $notifications) => count($notifications) === 2));

        $this->useCase->execute($userId, $ids, JobApplicationStatus::SHORTLISTED);
    }

    public function test_fails_entire_bulk_operation_if_one_status_cannot_transition(): void
    {
        $userId = 'user-123';
        $ids = ['app-valid', 'app-invalid'];

        $this->applicationRepo->method('getStatusesByIds')
            ->willReturn([
                'app-valid' => JobApplicationStatus::APPLIED,
                'app-invalid' => JobApplicationStatus::REJECTED, // Impossible de transitionner
            ]);

        // Aucune écriture en BDD ne doit être déclenchée
        $this->applicationRepo->expects($this->never())->method('bulkChangeStatus');
        $this->notificationRepo->expects($this->never())->method('saveAll');

        $this->expectException(\DomainException::class);

        $this->useCase->execute($userId, $ids, JobApplicationStatus::SHORTLISTED);
    }
}

