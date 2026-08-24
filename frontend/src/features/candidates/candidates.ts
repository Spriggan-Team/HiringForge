import type { Location, Skill } from "../shared/global";


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






export interface CandidateProfile {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  description: string;
  location: Omit<Location, 'id'>;
  skills: Skill[];
  image?: File;
}


export interface CandidateLightModel {
  id: string;
  firstName: string;
  lastName: string;
  avatarUrl?: string;
}


export interface ResumeFileMetada{
    id?: string;
    fileId?: string;
    name: string;
    size: number;
    mime: string;
    originalName?: string;
    createdAt: Date ;
}