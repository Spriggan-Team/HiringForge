
export type NotificationType = 
  | 'JOB_APPLIED' 
  | 'INTERVIEW_SCHEDULED' 
  | 'CANDIDATE_REJECTED' 
  | 'SYSTEM_ALERT';

export interface Notification {
  id: string;
  userId: string;
  type: NotificationType;
  title: string;
  message: string;
  targetUrl?: string;
  data?: Record<string, unknown>; 
  isRead: boolean;
  readAt?: string; // ISO Date String
  createdAt: string; // ISO Date String
}


