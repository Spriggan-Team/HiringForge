
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
  OFFER_DECLINED: 'offer_declined',
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



export type ApplicationStatusValue =
  (typeof JobApplicationStatus)[keyof typeof JobApplicationStatus];


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

  // Terminal statuses (no further action possible)
  [JobApplicationStatus.OFFER_DECLINED]: [],
  [JobApplicationStatus.HIRED]: [],
  [JobApplicationStatus.REJECTED]: [],
  [JobApplicationStatus.WITHDRAWN]: [],
};



export interface Application {
  id: string;
  candidate: string;
  email: string;
  appliedAt: string;
  status: ApplicationStatusValue;
  avatarUrl?: string;
  matchScore: number;
}