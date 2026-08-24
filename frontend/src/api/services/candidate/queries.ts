
import { intercept } from "../../../utils/utils";
import { authGet,  } from "../../http";

import type { ApiResponse, ErrorApiResponse } from "../response.types";

import type { CurrentCandidateContextResponse, GetResumeCollection } from "./responses";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import type { Skill } from "../../../features/shared/global";



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
            '/applications/jobs/ids'
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