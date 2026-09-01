import type { InterviewStatusValue } from "../../../features/interviews/interviews";
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet } from "../../http";

import type { ApiResponse, ErrorApiResponse } from "../response.types";
import type { CalendarInterviewsCollectionResponse, RecruiterJobInterviewsDetails, RecruiterJobInterviewsResponse } from "./response";



/**
 * Retreive interviews for today
 */
const getInterviewAgendaForRecruiter = async ({
    date,
    skip = 0,
    limit = 2,
}: {
    date?: Date ;
    skip?: number;
    limit?: number;
} = {}) => {
    const params = new URLSearchParams();
    
    const cleanDate = date ?? new Date();
    const utcDate = new Date(Date.UTC(
        cleanDate.getFullYear(),
        cleanDate.getMonth(),
        cleanDate.getDate()
    ));
    
    params.set("date", utcDate.toISOString());
    params.set("skip", String(skip));
    params.set("limit", String(limit));

    const response = await authGet<RecruiterJobInterviewsResponse>(
        `/interviews/users/agenda?${params.toString()}`
    );
    
    return response.data;
};


/**
 * Same as getInterviewsAgenda. But it does bnot sustend pagination param
 */
const getRecruiterInterviewsByDays = async ()=>{
    try{
        const response = await authGet<RecruiterJobInterviewsDetails>(`/interviews/users/calendar/day`);
        return response.data
    }
    catch(error){
        throw error;
    }
}



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



/**
 * Retreive with pagination for recruiter
 * @param param0 
 * @returns 
 */
const getRecruiterJobOfferInterviews = async (
   {
    jobId,
    companyId,
    skip,
    limit,
    statuses,
    search,
    signal
  }: {
    jobId?: string;
    companyId?: string;
    skip?: number;
    limit?: number;
    statuses?: InterviewStatusValue[] | null,
    signal?: AbortSignal,
    search?: string
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

/**
 * Get calendar planing for recriter
 */
const getRecruiterCalendarPlaning = async({
    currentMonth
}:{
    currentMonth: Date
})=>{
    try{
        if(!currentMonth){
            console.warn("currentMonth param is mandatory for fetching interviews calendar views")
            throw new Error();
        }

        const params = new URLSearchParams();
        params.set("currentMonth", currentMonth.toISOString());

        const response = await authGet<CalendarInterviewsCollectionResponse>(`/interviews/users/calendar?${params.toString()}`);
        return response.data ?? [];
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

    getRecruiterCalendarPlaning,
    getRecruiterInterviewsByDays,

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