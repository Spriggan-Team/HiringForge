<?php

namespace App\Application\Usecases\Candidate;

use App\Api\Responder\ApiResponse;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterface;
use App\Domain\Candidate\Application\Application;
use App\Domain\File\FileType;
use App\Domain\Candidate\Application\LexicalResumeParser;

use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;


use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\Document\DocumentExtractorInterface;
use App\Domain\Candidate\Application\ResumeLexicalParserInterface;

use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Candidate\Application\ResumeAiParserInterface;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Shared\Skill\RequiredSkill;
use App\Domain\Shared\Skill\SkillMatcherServiceInterface;
use App\Domain\Shared\Skill\SkillScoreCalculator;



class ApplyToJobOffer
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository,
        private ApplicationRepositoryInterface $applicationRepository,
        
        private PathResolverInterface $pathResolver,
        private DocumentExtractorInterface $docExtractor,
        
        private ResumeAiParserInterface $resumeAiParser,
        private ResumeLexicalParserInterface $resumeLexicalParser,
        private SkillMatcherServiceInterface $skillMatcherServices,
        private SkillScoreCalculator $skillScoreCalculator,

        private JobOfferRepositoryInterface $jobRepository,
        private JobOfferQueryRepositoryInterface $jobQueryInterface,
    ){}


    /**
     * @param string $candidateId The is the candidate's id
     * @param string $offerId The is  the id of an job - offer
     * @param string $fileId  The file id related to the resume
     * @throws RessourceNotFound|\DomainException
     */
    public function execute(string $candidateId, string $offerId, string $fileId)
    {
        //-- Check if job permit applications
        if(!$this->jobRepository->canAcceptApplications($offerId)){
            throw new \DomainException(
                "This application cannot accept applications"
            );
        }

        //-- Assert relation
        $resume = $this->candidateRepository->getResumeFileForCandidate(
            candidateId: $candidateId,
            fileId: $fileId,
        );

        if ($resume === null) {
            throw new \DomainException(
                'Resume document was not found.'
            );
        }

        //-- Company context
        $companyId = $this->jobRepository->getCompanyId($offerId);

        //-- Extract cv
        $params = AccountStorageParams::resumes(
            candidateId: $candidateId,
            storedFileName: $resume->name
        );

        $absoluteDirectoryPath = $this->pathResolver->resolveTargetDirectory(
            params: $params,
            mimeType: $resume->mime
        ); 

        $absoluteFilePath = rtrim($absoluteDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $resume->name;

        ApiResponse::$logger->error("ABSOLUTE FILE PATH : " .$absoluteFilePath);

        $plainText = $this->docExtractor->extract(
            absolutePath: $absoluteFilePath,
        );

        //-- Lexical CV Parse
        $clearDocument  = $this->resumeLexicalParser->parse($plainText);

        //- Semactic CV Parse
        $artefact = $this->resumeAiParser->parse($clearDocument);

        //-- Skills Research
        $skillMatches = [];

        foreach ($artefact->skills as $skill) {
            $match = $this->skillMatcherServices->findMatching(
                text: $skill,
            );

            if ($match !== null) {
                $skillMatches[] = $match;
            }
        }

        //-- Skill Calcul score
        $requiredSkills = array_map(
            static fn (string $skillId): RequiredSkill =>
                new RequiredSkill($skillId),
            $this->jobQueryInterface->getSkillIdsByJobId($offerId),
        );

        $score = $this->skillScoreCalculator->calculate(
            requiredSkills: $requiredSkills,
            matches: $skillMatches,
        );

        //-- Build Application domain object
        $application = Application::create(
            candidateId: $candidateId,
            jobOfferId: $offerId,
            score: $score,
            companyId: $companyId
        );
    
        $this->applicationRepository->save($application);
    }
}