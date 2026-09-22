import type { 
  SymfonyDateTime
} from "../shared/global";

export class InterviewStatus {
  static readonly CLOSED = 'closed'; // indicate manual closure/cancellation (handled: ok)
  static readonly MISSED = 'missed';
  static readonly SCHEDULED = 'scheduled'; //(handled: ok)
  static readonly IN_PROGRESS = 'in_progress';
  static readonly COMPLETED = 'completed'; //(handled: ok)
}

export type InterviewStatusValue = (typeof InterviewStatus)[keyof typeof InterviewStatus];

export const INTERVIEW_STATUSES: InterviewStatusValue[] = [
  InterviewStatus.CLOSED,
  InterviewStatus.MISSED,
  InterviewStatus.SCHEDULED,
  InterviewStatus.IN_PROGRESS,
  InterviewStatus.COMPLETED,
];


export const InterviewType = {
  RH_INTERVIEWS: 'rh_interviews',
  TECHNICAL_INTERVIEWS: 'technical_interviews',
} as const;

export type InterviewTypeValue = (typeof InterviewType)[keyof typeof InterviewType];

export const INTERVIEW_TYPES: InterviewTypeValue[] = [
  InterviewType.RH_INTERVIEWS,
  InterviewType.TECHNICAL_INTERVIEWS
]


//--------------------------------------
//-- main interviews object projection
//--------------------------------------

export type InterviewWithCandidateData = BaseInterviewData & {
  candidate: CandidateDataForInterview;
};


export type InterviewWithCompanyData = BaseInterviewData & CompanyDataForInterview

//-----------------------
///- Utils/Composition items
//------------------------

export type BaseInterviewData = {
  id: string;
  title?:string,
  startDate: string; // ISO 8601
  minutes: number;
  status: InterviewStatus;
  description?: string;
  url?: string;
  type?: InterviewTypeValue;
  candidateApproval?: boolean
  rejectionReason?: string | null;
  job?:{
    id: string;
    title: string;
  }
};

export interface CandidateDataForInterview  {
  id:string;
  firstName: string;
  lastName: string;
  imageId?: number;
  email: string; 
}


export interface CompanyDataForInterview{
  company: {
    id: string;
    name: string;
    logoUrl?: string;
  }
}





//----------------
//-- UI
//-----------------

export interface Interview {
  id: string;
  candidate?: string;
  recruiter?: string;
  email: string;
  jobTitle: string;
  scheduledAt: string; // ISO
  locationOrLink?: string;
  status: InterviewStatus;
  avatarUrl?: string;
}


export interface CreateInterviewFormData {
  candidateId: string;
  title?: string;
  description?: string;
  scheduledAt: string;
  url?: string;
  type?: InterviewTypeValue;
  minutes: number;
  applicationId: string;
}