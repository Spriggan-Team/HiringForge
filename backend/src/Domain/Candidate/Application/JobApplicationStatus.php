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
    case INTERVIEW_SCHEDULED = 'interview_scheduled'; // Scheduled Maintenance
    case IN_INTERVIEW = 'in_interview';         // Interview phase currently underway (HR, Technical, etc.)
    case ASSESSMENT = 'assessment';             // Undergoing technical testing / case study

    // --- Final Steps  ---
    case OFFER_PENDING = 'offer_pending';       // Job offer sent (awaiting a response)
    case OFFER_ACCEPTED = 'offer_accepted';     // Offer accepted by the candidate
    case OFFER_DECLINED = 'offer_declined';     // Offer declined by the candidate
    case HIRED = 'hired';                       // Officiellement recruté / Embauché

    // --- Pipeline Outputs / Archiving ---
    case REJECTED = 'rejected';                 // Application rejected by the company
    case WITHDRAWN = 'withdrawn';               //  Candidate who withdrew from the process
}