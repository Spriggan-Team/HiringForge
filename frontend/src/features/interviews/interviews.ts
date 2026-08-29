export class InterviewStatus {
  static readonly CANCELLED = 'cancel';
  static readonly CLOSED = 'closed';
  static readonly MISSED = 'missed';
  static readonly SCHEDULED = 'scheduled';
  static readonly IN_PROGRESS = 'in_progress';
  static readonly COMPLETED = 'completed';
}

export type InterviewStatusValue =
  (typeof InterviewStatus)[keyof typeof InterviewStatus];

export const INTERVIEW_STATUSES: InterviewStatusValue[] = [
  InterviewStatus.CANCELLED,
  InterviewStatus.CLOSED,
  InterviewStatus.MISSED,
  InterviewStatus.SCHEDULED,
  InterviewStatus.IN_PROGRESS,
  InterviewStatus.COMPLETED,
];


export class InterviewType {
  static RH_INTERVIEWS = 'rh_interviews';
  static TECHNICAL_INTERVIEWS = 'technical_interviews';
}


export type InterviewTypeValue = (typeof InterviewType)[keyof typeof InterviewType];


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
}