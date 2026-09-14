
import { authGet,  } from "../../http";
import { intercept } from "../../../utils/utils";

import type { ApiResponse, ErrorApiResponse } from "../response.types";

import { handleGenericApiResponseAfter } from "../../api-response-handler";

import type { Skill } from "../../../features/shared/global";
import type { 
    ApplicationsViewResponse, 
    ApplicationViewDetialsResponse,
    CandidateApplicationStatsResponse,
    CurrentCandidateContextResponse,
    GetResumeCollection
} from "./responses";
import type { ApplicationMenu } from "../../../features/application/application";



/**
 * Get candidates Applications
 */
const getMyApplications = async ({
    skip = 0,
    limit = 10,
    menu,
}:{
    skip?: number;
    limit?: number;
    menu: ApplicationMenu;
})=>{
    const params = new URLSearchParams();
    
    if(skip) 
        params.set("skip", String(skip));
    if(limit) 
        params.set("limit", String(limit));
    if(menu) 
        params.set("menu", String(menu));

    const url = `/applications/candidate/my-applications?${params.toString()}`
    const response = await authGet<ApplicationsViewResponse>(url);

    return response.data
}


/**
 * Get Applications stats for candidate
 */
const getApplicationsSats = async ()=>{
    const response = await authGet<CandidateApplicationStatsResponse>(`/applications/candidate/stats`);
    return response.data;
}


const getApplicationViewDetails = async({
    applicationId,
    locale
}:{
    applicationId: string,
    locale?: string
})=>{
    const params = new URLSearchParams();
    if(locale) params.append("locale", String(locale));

    const url = `/applications/candidate/${applicationId}?${params.toString()}`;
    const response = await authGet<ApplicationViewDetialsResponse>(url);
    
    return response.data;
}


/**
 * Get description
 * @returns 
 */
const getCandidateDescription = async ()=>{
    try{
        const response = await authGet<ApiResponse<{desc: string}>>(`/candidates/profile/desc`)
        return response.data.desc;
    }
    catch(error){
        throw error;
    }
}



/**
 * Skills for candidates
 * @returns 
 */
const getCandidateSkills = async()=>{
    try{
        const response = await authGet<ApiResponse<Skill[]>>(`/candidates/profile/skills`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}



/**
 * Retreive job ids
 * related to the current candidate
 */
const getCandidateApplicationJobIds = async ():  Promise<Record<string, boolean>>=>{
    try{
        const response = await authGet<ApiResponse<string[]>>(
            '/applications/candidate/jobs/ids'
        );

        const map: Record<string, boolean> = {};

        for (const id of response.data) {
            map[id] = true;
        }

        return map;
    }
    catch(error)
    {
        throw error;
    }
}


/**
 * Retreive image profil
 * @param candidateId 
 * @returns 
 */
const getCandidateProfileImage = async(candidateId: string)=>{
    try{
        const blob = authGet<Blob>(`/candidates/assets/profile`);
        return blob;
    }
    catch(error){
        throw error;
    }
}


/**
 * Retreive candidate resume
 * @returns 
 */
const getResumes = async ()=> {
    try{
        const response  = await authGet<GetResumeCollection>(`/candidates/assets/me/resumes`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}



/**
 * Retreive candidate context
 * @returns 
 */
const getCurrentCandidateContext = async()=>{
    try{
        const response = await  authGet<CurrentCandidateContextResponse>(`/candidates`);
        return response.data
    }
    catch(error){
        throw error;
    }
}


/**
 * retreive resume ressoruce
 * @param resumeId 
 * @returns 
 */
const getResumeContent = async (resumeId: string)=>{
    try{
        const blob = await authGet<Blob>(`/candidates/assets/resumes/${resumeId}/content`);
        return blob;
    }
    catch(error){
        throw error;
    }
}



/**
 * Queries services
 */
const Queries = { 
    getResumes,
    getResumeContent,
    getCandidateProfileImage,

    getMyApplications,
    getApplicationsSats,
    getApplicationViewDetails,
    
    getCandidateSkills,
    getCandidateDescription,
    getCurrentCandidateContext,

    getCandidateApplicationJobIds
}

/**
 * Handler & api request for candidate queries
 */
const CandidatesQueries = intercept<
    typeof Queries,
    ApiResponse | ErrorApiResponse
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)


export default CandidatesQueries;