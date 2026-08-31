// ==========================================
//  Notification Data Types
// ==========================================


/** Interfaces for notification lined to an offer */
export interface BaseJobNotificationData {
  jobId: string;
  jobTitle: string;
}

export interface JobAppliedData extends BaseJobNotificationData {
  candidateId: string;
  candidateName: string;
}

export interface InterviewScheduledData extends BaseJobNotificationData {
  scheduledAt: string; // ISO 8601 String
  locationOrLink?: string;
  recruiterName?: string;
}


export interface CandidateRejectedData extends BaseJobNotificationData {
  companyName: string;
  reason?: string;
}


//-- employment notif
export interface EmploymentOfferNotificationData {
  employmentOfferId: string;
  jobTitle: string;
  companyName: string;
  salary?: number | null;
  expiresAt?: string | null;
}


//-- stystem
export type SystemAlertLevel = 'info' | 'warning' | 'danger';


export interface SystemAlertData {
  title: string;
  message: string;
  level: SystemAlertLevel;
  metadata?: Record<string, unknown>;
}


// ==========================================
//  Discriminated Notification Unions
// ==========================================


export const Notifications = {
  JOB_APPLIED: 'JOB_APPLIED',
  JOB_APPLICATIONS_STATUS_SHIFT: 'JOB_APPLICATIONS_STATUS_SHIFT',
  
  //-- Interviews
  INTERVIEW_SCHEDULED: 'INTERVIEW_SCHEDULED',
  
  //-- Application
  CANDIDATE_REJECTED: 'CANDIDATE_REJECTED',

  //-- Employment
  EMPLOYMENT_OFFER_GENERATED: "EMPLOYMENT_OFFER_GENERATED",
  EMPLOYMENT_OFFER_CANCELLED:  "EMPLOYMENT_OFFER_CANCELLED",
    
  SYSTEM_ALERT:  'SYSTEM_ALERT'
} as const;

export type NotificationValueType = typeof Notifications[keyof typeof Notifications];


interface BaseNotification {
  id: string;
  isRead: boolean;
  account?:{
    id?: string;
    firstName: string;
    lastName: string;
  },
  targetUrl?: string | null;
  readAt?: string | null;
  createdAt: string;
}


export type JobAppliedNotification = BaseNotification & {
  type: 'JOB_APPLIED';
  data: JobAppliedData;
};


export type InterviewScheduledNotification = BaseNotification & {
  type: 'INTERVIEW_SCHEDULED';
  data: InterviewScheduledData;
};


export type CandidateRejectedNotification = BaseNotification & {
  type: 'CANDIDATE_REJECTED';
  data: CandidateRejectedData;
};


export type EmploymentOfferNotification = BaseNotification & {
  type: 
    | typeof Notifications.EMPLOYMENT_OFFER_GENERATED 
    | typeof Notifications.EMPLOYMENT_OFFER_CANCELLED;
  data: EmploymentOfferNotificationData;
};


export type SystemAlertNotification = BaseNotification & {
  type: 'SYSTEM_ALERT';
  data: SystemAlertData;
};


//------------------------------------
//-- Complete Notifications Types
//------------------------------------

export type JobNotification = JobAppliedNotification;

/** Type Global for any notfication  */
export type NotificationTypes =
  | JobAppliedNotification
  | InterviewScheduledNotification
  | CandidateRejectedNotification
  | SystemAlertNotification
  | EmploymentOfferNotification;




