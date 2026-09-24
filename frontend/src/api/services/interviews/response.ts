import type {  
    BaseInterviewData, 
    InterviewStatus, 
    InterviewStatusValue,
    InterviewTypeValue,
    InterviewWithCandidateData, 
    InterviewWithCompanyData
} from "../../../features/interviews/interviews";
import type { ApiResponse } from "../response.types";


//--- Jobd interviews collections (with details)
export type RecruiterJobInterviewsResponse = ApiResponse<InterviewWithCandidateData[]>;
export type CandidateJobInterviewsResponse = ApiResponse<InterviewWithCompanyData[]>;

//-- Calendar Job interviews (Date key: Y-m-d)
export type CalendarInterviewsCollectionResponse = ApiResponse<Record<string, {
    id: string;
    title?: string;
    minutes: number;
    startDate: string;
    url?:string;
    type?: InterviewTypeValue,
    status: InterviewStatusValue
}[]>>



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