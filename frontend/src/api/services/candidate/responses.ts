import type { ApiResponse } from "../response.types";



export type CurrentCandidateContextResponse = ApiResponse<CurrentUser>;


export interface CurrentUser {
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    imageUrl: string | null;
}