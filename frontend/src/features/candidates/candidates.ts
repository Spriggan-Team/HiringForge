

interface Candidate {
    stage: CandidateStage;
    status: CandidateStatus;
}

export type CandidateStage =
    | "applied"
    | "screening"
    | "hr-interview"
    | "assessment"
    | "technical-interview"
    | "final-interview"
    | "reference-check"
    | "offer"
    | "offer-accepted"
    | "hired";

export type CandidateStatus =
    | "active"
    | "on-hold"
    | "withdrawn"
    | "rejected"
    | "hired";


export interface CandidateLightModel {
  id: string;
  firstName: string;
  lastName: string;
  avatarUrl?: string;
}


export interface ResumeFileMetada{
    id: string;
    name: string;
    size: number;
    mime: string;
    originalName?: string;
    createdAt: Date ;
}