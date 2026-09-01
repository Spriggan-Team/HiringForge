import type { InterviewStatusValue } from "../../../features/interviews/interviews";
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet } from "../../http";

import type { ApiResponse, ErrorApiResponse } from "../response.types";
import type { RecruiterJobInterviewsResponse } from "./response";


/**
 * Retreive interviews for today
 */
const getInterviewAgendaForRecruiter = async ({
    date,
    skip = 0,
    limit = 2,
}: {
    date?: Date;
    skip?: number;
    limit?: number;
} = {}) => {
    const params = new URLSearchParams();
    
    const targetDate = date ?? new Date();
    
    params.set("date", targetDate.toISOString());
    params.set("skip", String(skip));
    params.set("limit", String(limit));

    const response = await authGet<RecruiterJobInterviewsResponse>(
        `/interviews/users/agenda?${params.toString()}`
    );
    
    return response.data;
};


/**
 * Retreeiving candiate image using interviews as base
 */
const getCandidateImage = async ({
    candidateId, 
    interviewId
}:{
    candidateId: string,
    interviewId: string
})=>{
    try{
        const params = new URLSearchParams();

        if (candidateId) params.set('candidateId', candidateId);
        if (interviewId) params.set('interviewId', String(interviewId));
        
        const url = `/interviews/users/candidate/image?${params.toString()}`;
        const blob = await authGet<Promise<Blob>>(url);

        return blob;
    }
    catch(error){
        throw error;
    }
}


//Recruiter
const getRecruiterJobOfferInterviews = async (
   {
    jobId,
    companyId,
    skip,
    limit,
    statuses,
    signal
  }: {
    jobId?: string;
    companyId?: string;
    skip?: number;
    limit?: number;
    statuses?: InterviewStatusValue[] | null,
    signal?: AbortSignal
  } = {}
)=>{
    try{
        const params = new URLSearchParams();

        if (companyId) params.set('companyId', companyId);
        if (skip) params.set('skip', String(skip));
        if (limit) params.set('limit', String(limit));
        if (jobId) params.set('jobId', jobId)
        if (statuses) params.set('statuses', JSON.stringify(statuses))

        const url = `/interviews/users/job_offers${
            params.toString() ? `?${params.toString()}` : ''
        }`;

        const response = await authGet<RecruiterJobInterviewsResponse>(url, {}, {signal});
        return response.data;
    }
    catch(error){
        throw error;
    }
}


//-----
//----Services

const Queries = {
    getRecruiterJobOfferInterviews,
    getInterviewAgendaForRecruiter,
    getCandidateImage
}


const InterviewsQueries = intercept<
    typeof Queries,
    ApiResponse | ErrorApiResponse
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
);


export default InterviewsQueries;