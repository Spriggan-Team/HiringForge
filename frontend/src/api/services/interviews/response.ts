import type {  BaseInterviewData, InterviewStatus, InterviewStatusValue, InterviewTypeValue, InterviewWithCandidateData } from "../../../features/interviews/interviews";
import type { ApiResponse } from "../response.types";


//--- Jobd interviews collections
export type RecruiterJobInterviewsResponse = ApiResponse<InterviewWithCandidateData[]>;


//-- Calendar Job interviews (Date key: Y-m-d)
export type CalendarInterviewsCollectionResponse = ApiResponse<Record<string,{
    id: string;
    title?: string;
    minutes: number;
    startDate: string;
    type?: InterviewTypeValue,
    status: InterviewStatusValue
}[]>>


export type RecruiterJobInterviewsDetails = ApiResponse<BaseInterviewData>;


export interface InterviewDetails {
    id: string;
    startDate: string;
    title: string | null;
    type: InterviewTypeValue | null;
    status: InterviewStatusValue;
    description: string;
    candidateApproval: boolean;
    createdAt: string;
}