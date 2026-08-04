


export type NotificationType = 
  | 'JOB_APPLIED' 
  | 'INTERVIEW_SCHEDULED' 
  | 'CANDIDATE_REJECTED' 
  | 'SYSTEM_ALERT';


export type Notification<T = Record<string, unknown>> = {
  id: string;
  userId: string;
  type: NotificationType;
  targetUrl?: string;
  data?: T; 
  isRead: boolean;
  readAt?: string; // ISO Date String
  createdAt: string; // ISO Date String
}


//------------------------------------
//--- Notification Data Scheme
//--------------------------------------

//-- Job Application
export interface JobAppliedData {
  jobId: string;
  jobTitle: string;
  candidateId: string;
  candidateName: string;
  companyId?: string;
}


//-- Interview
export type InterviewScheduledData = {
  jobTitle: string;
  scheduledAt: string; // ISO 
  locationOrLink?: string;
  recruiterName?: string;
};


//-- Alert Sys

export type SystemAlertLevel = 'info' | 'warning' | 'danger';

export type SystemAlertData = {
  title: string;
  message: string;
  level: SystemAlertLevel;
  metadata?: Record<string, unknown>;
};