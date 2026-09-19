import type { InterviewStatusValue, InterviewTypeValue } from "../interviews/interviews";

export const JobApplicationStatus = {
  // --- Entry Phase ---
  APPLIED: 'applied',
  RECEIVED: 'received',
  SHORTLISTED: 'shortlisted',

  // --- Evaluation Phase ---
  SCREENING: 'screening',
  INTERVIEW_SCHEDULED: 'interview_scheduled',
  IN_INTERVIEW: 'in_interview',
  ASSESSMENT: 'assessment',

  // --- Final Steps ---
  OFFER_PENDING: 'offer_pending',
  OFFER_ACCEPTED: 'offer_accepted',
  OFFER_DECLINED: 'offer_declined',  // Offer declined by the candidate
  OFFER_EXPIRED: 'offer_expired',   
  HIRED: 'hired',

  // --- Pipeline Outputs / Archiving ---
  REJECTED: 'rejected',
  WITHDRAWN: 'withdrawn',
} as const;


export const JOB_APPLICATION_STATUSES: ApplicationStatusValue[] = [
  'applied',
  'received',
  'shortlisted',
  'screening',
  'interview_scheduled',
  'in_interview',
  'assessment',
  'offer_pending',
  'offer_accepted',
  'offer_declined',
  'hired',
  'rejected',
  'withdrawn',
] as const;



export type ApplicationStatusValue = (typeof JobApplicationStatus)[keyof typeof JobApplicationStatus];


export type TerminalApplicationStatus =
  | typeof JobApplicationStatus.HIRED
  | typeof JobApplicationStatus.REJECTED
  | typeof JobApplicationStatus.WITHDRAWN
  | typeof JobApplicationStatus.OFFER_DECLINED;


//  Mapping of Permitted Transitions (State Machine)
export const ALLOWED_STATUS_TRANSITIONS: Record<ApplicationStatusValue, ApplicationStatusValue[]> = {
  [JobApplicationStatus.APPLIED]: [
    JobApplicationStatus.RECEIVED,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.RECEIVED]: [
    JobApplicationStatus.SHORTLISTED,
    JobApplicationStatus.SCREENING,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.SHORTLISTED]: [
    JobApplicationStatus.SCREENING,
    JobApplicationStatus.INTERVIEW_SCHEDULED,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.SCREENING]: [
    JobApplicationStatus.INTERVIEW_SCHEDULED,
    JobApplicationStatus.ASSESSMENT,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.INTERVIEW_SCHEDULED]: [
    JobApplicationStatus.IN_INTERVIEW,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.IN_INTERVIEW]: [
    JobApplicationStatus.ASSESSMENT,
    JobApplicationStatus.INTERVIEW_SCHEDULED, // To schedule a second or third interview
    JobApplicationStatus.OFFER_PENDING,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.ASSESSMENT]: [
    JobApplicationStatus.INTERVIEW_SCHEDULED,
    JobApplicationStatus.OFFER_PENDING,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.OFFER_PENDING]: [
    JobApplicationStatus.OFFER_ACCEPTED,
    JobApplicationStatus.OFFER_DECLINED,
    JobApplicationStatus.REJECTED, // Cancellation of the offer by the company
    JobApplicationStatus.WITHDRAWN,
  ],
  [JobApplicationStatus.OFFER_ACCEPTED]: [
    JobApplicationStatus.HIRED,
    JobApplicationStatus.WITHDRAWN, // Last-minute cancellation
  ],
  [JobApplicationStatus.OFFER_EXPIRED]: [],

  // Terminal statuses (no further action possible)
  [JobApplicationStatus.OFFER_DECLINED]: [],
  [JobApplicationStatus.HIRED]: [],
  [JobApplicationStatus.REJECTED]: [],
  [JobApplicationStatus.WITHDRAWN]: [],
};


//---------------------
//--- UI & API
//--------------------

export interface Application {
  id: string;
  candidate: string;
  candidateId: string;
  email: string;
  appliedAt: string;
  status: ApplicationStatusValue;
  matchScore: number;
  
  interviews?: {
    id: string;
    startDate: string; // ISO
    type: InterviewTypeValue;
    minutes: number;
    status: InterviewStatusValue
  }[]
}


//---------------------
//-- Candidate only
//--------------------
export interface CandidateApplicationStats
{
  hiredCount: number;
  pendingCount: number;
  interviewCount: number;
  completedCount: number;
  applicationCount: number;
}

//--------------------
//-- API Only
//-------------------------


/**
 * Light weight model for applications collection
 */
export interface ApplicationView{
    id: string;
    job:{
        title: string
        image: string;
    }
    company:{
        name: string;
        logo: string;
    },
    department?:{
      id: number;
      name: string; //-- label
    };
    statuses: ApplicationStatusValue[]; //-- used here (statuses relative to interview, decision or pending)
    appliedAt: string;    
}


/**
 * -------------------------------------
 * APPLICATION DETAILS (FOR CANDIDATE)
 * -------------------------------------
 */

/**
 * Represents the complete application data by combining
 * the application overview with its detailed information.
 */
export type ApplicationDetails = ApplicationView & ApplicationViewDetails;


export interface ApplicationViewDetails {
    contractType: string;

    location: {
        city?: string;
        street?: string;
        postalCode?: string;
        country?: string;
    };

    content: Record<string, unknown>;

    skills: Array<{
        id: string;
        name: string;
    }>;
}

export type ApplicationMenu =
    | "ALL"
    | "PENDING"
    | "INTERVIEW"
    | "COMPLETED";