<?php


namespace App\Domain\Candidate\Application;


enum JobApplicationStatus: string
{
    // --- Entry Phase  ---
    case APPLIED = 'applied';                   // Application submitted by the candidate
    case RECEIVED = 'received';                 // Received/Recorded
    case SHORTLISTED = 'shortlisted';           // Shortlisted by the recruiter

    // --- Evaluation Phase ---
    case SCREENING = 'screening';               // Initial Screening / Telephone Prequalification
    case PRESELECTED = 'preselected';
    case INTERVIEW_SCHEDULED = 'interview_scheduled'; // Scheduled Maintenance
    case IN_INTERVIEW = 'in_interview';         // Interview phase currently underway (HR, Technical, etc.)
    case ASSESSMENT = 'assessment';             // Undergoing technical testing / case study ..ect

    // --- Final Steps  ---
    case OFFER_PENDING = 'offer_pending';       // Job offer sent (awaiting a response)
    case OFFER_ACCEPTED = 'offer_accepted';     // Offer accepted by the candidate
    case OFFER_DECLINED = 'offer_declined';     // Offer declined by the candidate
    case HIRED = 'hired';                       // Officiellement recruté / Embauché

    // --- Pipeline Outputs / Archiving ---
    case REJECTED = 'rejected';                 // Application rejected by the company
    case WITHDRAWN = 'withdrawn';               //  Candidate who withdrew from the process


    public function canTransitionTo(self $newStatus): bool
    {
        if ($this === $newStatus) {
            return true;
        }

        // Defining the Matrix of Allowed Transitions in PHP
        $allowedTransitions = [
            self::APPLIED->value => [self::RECEIVED, self::REJECTED, self::WITHDRAWN],
            self::RECEIVED->value => [self::SHORTLISTED, self::SCREENING, self::REJECTED, self::WITHDRAWN],
            self::SHORTLISTED->value => [self::SCREENING, self::INTERVIEW_SCHEDULED, self::REJECTED, self::WITHDRAWN],
            self::SCREENING->value => [self::INTERVIEW_SCHEDULED, self::ASSESSMENT,  self::REJECTED, self::WITHDRAWN],
            self::INTERVIEW_SCHEDULED->value => [self::IN_INTERVIEW, self::REJECTED, self::WITHDRAWN],
            self::IN_INTERVIEW->value => [self::ASSESSMENT, self::INTERVIEW_SCHEDULED,  self::OFFER_PENDING, self::REJECTED, self::WITHDRAWN],
            self::ASSESSMENT->value => [self::INTERVIEW_SCHEDULED, self::OFFER_PENDING, self::REJECTED, self::WITHDRAWN],
            self::OFFER_PENDING->value => [self::OFFER_ACCEPTED, self::OFFER_DECLINED,  self::REJECTED, self::WITHDRAWN],
            self::OFFER_ACCEPTED->value => [self::HIRED, self::WITHDRAWN],
            
            // Statuts terminaux
            self::OFFER_DECLINED->value => [],
            self::HIRED->value => [],
            self::REJECTED->value => [],
            self::WITHDRAWN->value => [],
        ];

        return in_array($newStatus, $allowedTransitions[$this->value] ?? [], true);
    }
}