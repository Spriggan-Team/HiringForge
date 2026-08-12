import type { ResumeMetada } from "../../../features/candidates/candidates";
import type { ApiResponse } from "../response.types";



export type CurrentCandidateContextResponse = ApiResponse<CurrentUser>;
export type GetResumeCollection = ApiResponse<ResumeMetada[]>;


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


