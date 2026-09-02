import type { ApplicationStatusValue, ApplicationView, ApplicationViewDetails, CandidateApplicationStats } from "../../../features/application/application";
import type { ResumeFileMetada } from "../../../features/candidates/candidates";
import type { ApiResponse } from "../response.types";


//---------------
//--- Account & File
//-----------------

export type CurrentCandidateContextResponse = ApiResponse<CurrentUser>;
export type GetResumeCollection = ApiResponse<ResumeFileMetada[]>;

export interface CurrentUser {
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    imageUrl: string | null;
    
    address:{
        id?: number;
        city: string;
        country:  string;
        postalCode: string;
        street?: string;
    }
}

//---------------
//--- Applications
//-----------------

export type ApplicationsViewResponse = ApiResponse<PaginatedApplicationsResponse>;

export type CandidateApplicationStatsResponse = ApiResponse<CandidateApplicationStats>;

export type ApplicationViewDetialsResponse = ApiResponse<ApplicationViewDetails>;


interface PaginatedApplicationsResponse {
    data: ApplicationView[];
    total: number;
}