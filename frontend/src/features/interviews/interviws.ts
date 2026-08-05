export type InterviewStatus =
    | "cancel"
    | "closed"
    | "missed"
    | "scheduled"
    | "in_progress"
    | "completed";

export const INTERVIEW_STATUSES: InterviewStatus[] = [
    "cancel",
    "closed",
    "missed",
    "scheduled",
    "in_progress",
    "completed"
];

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