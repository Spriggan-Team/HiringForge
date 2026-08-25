<?php

namespace App\Application\Usecases\Candidate;

use App\Api\Responder\ApiResponse;
use App\Domain\Candidate\Application\Application;

use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Document\DocumentExtractorInterface;
use App\Domain\Candidate\Application\ResumeLexicalParserInterface;

use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Candidate\Application\ResumeAiParserInterface;
use App\Domain\Candidate\CandidateResume;
use App\Domain\Candidate\CandidateResumeRepositoryInterface;
use App\Domain\Candidate\CandidateSkillRepositoryInterface;

use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterface;
use App\Domain\Candidate\Application\CVParserInterface;
use App\Domain\JobOffer\JobOfferRepositoryInterface;

use App\Domain\Notification\JobAppliedNotificationData;
use App\Domain\Notification\Notification;

use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;
use App\Domain\Shared\Skill\RequiredSkill;
use App\Domain\Shared\Skill\SkillMatch;
use App\Domain\Shared\Skill\SkillMatcherServiceInterface;
use App\Domain\Shared\Skill\SkillMatchMethod;
use App\Domain\Shared\Skill\SkillScoreCalculator;

use Psr\Log\LoggerInterface;


class ApplyToJobOffer
{
    public function __construct(
        private CandidateSkillRepositoryInterface $candidateSkillRepository,
        private CandidateResumeRepositoryInterface $candidateResumeRepository,

        private CandidateRepositoryInterface $candidateRepository,
        private ApplicationRepositoryInterface $applicationRepository,
        
        private PathResolverInterface $pathResolver,
        private DocumentExtractorInterface $docExtractor,
        
        private CVParserInterface $cvParser,
        private SkillMatcherServiceInterface $skillMatcherServices,
        private SkillScoreCalculator $skillScoreCalculator,

        private JobOfferRepositoryInterface $jobRepository,
        private JobOfferQueryRepositoryInterface $jobQueryInterface,
        private NotificationRepositoryInterface $notificationRepository,

        private LoggerInterface $logger
    ){}


    /**
     * Apply to a job offer.
     *
     * The resume is analyzed only once. Subsequent applications
     * reuse the skills already extracted from the candidate's resume.
     *
     * @throws RessourceNotFound|\DomainException
     */
    public function execute(string $candidateId, string $offerId, string $fileId): string
    {
        // --------------------------------------------------------
        //-- Check application requirements
        // ---------------------------------------------------------
        if (!$this->jobRepository->canAcceptApplications($offerId)) {
            throw new \DomainException(
                'This job offer is no longer accepting applications.'
            );
        }

        if (!$this->candidateRepository->canApply(
            candidateId: $candidateId,
            jobId: $offerId
        )) {
            throw new \DomainException(
                'The current user has already applied to this job.'
            );
        }

        // ---------------------------------------------------------
        //-- Assert that the resume belongs to the candidate
        //----------------------------------------------------------

        $candidateResumeId = $this->candidateResumeRepository->findCandidateResumeIdByFileId(fileId: $fileId);
        if ($candidateResumeId === null) {
            throw new \DomainException(
                'Candidate resume not found.'
            );
        }

        $resume = $this->candidateRepository->getResumeFileForCandidate(
            candidateId: $candidateId,
            fileId: $fileId,
        );

        if ($resume === null) {
            throw new \DomainException(
                'Resume document was not found.'
            );
        }

        // ---------------------------------------------------------
        //-- Company context
        // ---------------------------------------------------------

        $companyId = $this->jobRepository->getCompanyId($offerId);
        ApiResponse::$logger->error("OFFER ID: ". $offerId);
        ApiResponse::$logger->error("COMPANY ID: ". $companyId);
        ApiResponse::$logger->error("Message (fielId): ". $fileId);

        // ---------------------------------------------------------
        //-- Get required skills for the job
        // ---------------------------------------------------------

        $requiredSkills = array_map(
            static fn (string $skillId): RequiredSkill =>
                new RequiredSkill($skillId),
            $this->jobQueryInterface->getSkillIdsByJobId($offerId),
        );

        $score = 0;

        // ---------------------------------------------------------
        //-- Analyze / reuse resume skills
        // ---------------------------------------------------------

        if($requiredSkills !== [])
        {
            $skillMatches = [];

            $hasBeenAnalyzed = $candidateResumeId !== null
                && $this->candidateResumeRepository->hasBeenAnalyzed(
                    candidateId: $candidateId,
                    fileId: $fileId,
                );
 
            if(!$hasBeenAnalyzed){
                // -------------------------------------------------
                //-- Extract & Parse cv
                // -------------------------------------------------

                $params = AccountStorageParams::resumes(
                    candidateId: $candidateId,
                    storedFileName: $resume->name
                );

                $absoluteFilePath = $this->pathResolver->resolveFilePath(
                    params: $params,
                    mimeType: $resume->mime
                ); 

                $artefact = $this->cvParser->parse($absoluteFilePath);

                // -------------------------------------------------
                // Mark  resume as analysized
                // -------------------------------------------------

                $this->candidateResumeRepository->markAsParsed($candidateResumeId);

                // -------------------------------------------------
                //  Resolve and persist candidate skills
                // -------------------------------------------------

                foreach ($artefact->skills as $skill) {
                    $match = $this->skillMatcherServices->findMatching(
                        text: $skill,
                        candidateId: $candidateId,
                        enableVectorMatch: true,
                    );

                    if ($match === null) {
                        continue;
                    }

                    $skillMatches[] = $match;

                    $this->candidateSkillRepository->link(
                        candidateId: $candidateId,
                        skillId: $match->skillId,
                        candidateResumeId: $candidateResumeId,
                    );
                }

            }
            else{
                // -------------------------------------------------
                // Resume already analyzed
                // -------------------------------------------------

                $candidateSkills = $this->candidateSkillRepository->getCandidateSkills(
                    candidateId: $candidateId,
                );

                $requiredSkillIds = array_fill_keys(
                    array_map(
                        static fn (RequiredSkill $skill): string => $skill->skillId,
                        $requiredSkills,
                    ),
                    true,
                );

                foreach ($candidateSkills as $candidateSkill) {
                    if (!isset($requiredSkillIds[$candidateSkill->id()])) {
                        continue;
                    }

                    $skillMatches[] = new SkillMatch(
                        skillId: $candidateSkill->id(),
                        method: SkillMatchMethod::EXACT,
                    );
                }

                // -----------------------------------------------------
                //  Calculate application score
                // -----------------------------------------------------

                $score = $this->skillScoreCalculator->calculate(
                    requiredSkills: $requiredSkills,
                    matches: $skillMatches,
                );
            }

        }
            
        // ---------------------------------------------------------
        // 7. Create application
        // ---------------------------------------------------------

        $application = Application::create(
            candidateId: $candidateId,
            jobOfferId: $offerId,
            score: $score,
            companyId: $companyId,
            candidateResumeId: $candidateResumeId ?? null
        );

        $applicationId = $this->applicationRepository->save(
            $application,
        );

        // ---------------------------------------------------------
        //  Create notification
        // ---------------------------------------------------------

        try {
            $jobTitle = $this->jobRepository->getTitle($offerId);

            $candidateName =
                $this->candidateRepository->getFullName($candidateId);

            $recruiterId =
                $this->jobRepository->getAuthorId(
                    jobId: $offerId,
                );

            $notification = Notification::create(
                accountId: $candidateId,
                recipientId: $recruiterId,
                recipientCompanyId: $companyId,
                type: NotificationType::JOB_APPLIED,
                data: JobAppliedNotificationData::create(
                    jobId: $offerId,
                    jobTitle: $jobTitle,
                    candidateId: $candidateId,
                    candidateName: $candidateName,
                    companyId: $companyId,
                ),
            );

            $this->notificationRepository->save(
                $notification,
            );
        }
        catch (\Throwable $e) {
            $this->logger->error(
                'Failed to create application notification.',
                [
                    'candidateId' => $candidateId,
                    'offerId' => $offerId,
                    'exception' => $e,
                ],
            );
        }


        return $applicationId;
    }
}