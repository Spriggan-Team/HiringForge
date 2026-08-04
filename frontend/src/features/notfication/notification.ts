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

export type NotificationType =
  | 'JOB_APPLIED'
  | 'INTERVIEW_SCHEDULED'
  | 'CANDIDATE_REJECTED'
  | 'SYSTEM_ALERT';

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

export type AppNotificationType = 
  | 'JOB_APPLIED' | 'INTERVIEW_SCHEDULED' 
  | 'CANDIDATE_REJECTED' | 'SYSTEM_ALERT';

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

export type SystemAlertNotification = BaseNotification & {
  type: 'SYSTEM_ALERT';
  data: SystemAlertData;
};

/** Type Global for any notfication  */

export type Notification =
  | JobAppliedNotification
  | InterviewScheduledNotification
  | CandidateRejectedNotification
  | SystemAlertNotification;




