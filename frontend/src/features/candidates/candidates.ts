

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