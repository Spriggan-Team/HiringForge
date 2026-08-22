import type { InterviewStatus } from "../../../features/interviews/interviews";
import type { ApiResponse } from "../response.types";

export type RecruiterJobInterviewsResponse =
    ApiResponse<InterviewWithCandidateData[]>;

export type BaseInterviewData = {
    id: string;
    startDate: string; // ISO 8601
    minutes: number;
    status: InterviewStatus;
    description: string;
    url: string;
};

export type CandidateImageData = {
    name: string;
    mime: string;
};

export type CandidateData = {
    firstName: string;
    lastName: string;
    email: string;
    image?: CandidateImageData | null;
};

export type InterviewWithCandidateData = BaseInterviewData & {
    candidate: CandidateData;
};

export type InterviewWithRecruiterData = BaseInterviewData & {
    // recruiter: RecruiterData;
};