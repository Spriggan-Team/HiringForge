import type { InterviewStatusValue } from "../../../features/interviews/interviews";
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet } from "../../http";

import type { ApiResponse, ErrorApiResponse } from "../response.types";
import type { CalendarInterviewsCollectionResponse, CandidateJobInterviewsResponse, RecruiterJobInterviewsResponse } from "./response";


/**
 * ----------------------------
 * Candidate
 * ----------------------------
 */


/**
 * Retrieves the interview agenda for a candidate.
 *
 * Unlike a standard collection view, this function also provides
 * detailed information for each interview, such as the description,
 * rejection reason, and  information about the associated recruiter.
 * 
  * Pick interviews planned on the provided date
 */
const getInterviewAgendaForCandidate = async ({
    skip = 0, 
    limit = 15, 
    date,
    statuses = []
}:{
    skip: number;
    limit: number; 
    date?: Date;
    statuses?: InterviewStatusValue[];
})=>{
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
    params.set("statuses", String(statuses));

    const url = `/interviews/candidates/agenda?${params.toString()}`

    const response = await authGet<CandidateJobInterviewsResponse>(url);

    return response.data;
}


/**
 * Retrieves the interview calendar for a candidate.
 *
 * It retrieves a lightweight collection of interviews
 * and organizes them by day for the current month.
 * Only the data required to display the calendar is retrieved.
 */
const getCandidateCalendarPlaning = async ({
    currentMonth
}:{
    currentMonth: Date
})=>{
    const params = new URLSearchParams();
    params.set("currentMonth", currentMonth.toISOString());

    const url = `/interviews/candidates/calendar?${params.toString()}`
    const response = await authGet<CalendarInterviewsCollectionResponse>(url);


    return response.data;
}




/**
 * ----------------------------
 * Recruiter
 * ----------------------------
 */



/**
 * Retrieves the interview agenda for a recruiter.
 *
 * Unlike a standard collection view, this function also retrieves
 * detailed information for each interview, such as the description,
 * rejection reason, and a lightweight representation of the associated candidate.
 * 
 * Pick interviews planned on the provided date
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
 * Retreeiving candiate image using interview relation
 * as source of truth
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
 * Retrieves interviews for a specific job with pagination.
 *
 * If no job ID is provided, the query retrieves all interviews
 * associated with the current recruiter.
 *
 * @param jobId Optional ID of the job to filter interviews by.
 * @param skip Number of interviews to skip for pagination.
 * @param limit Maximum number of interviews to retrieve.
 * @returns A paginated collection of interviews.
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
    jobId?: string | null;
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
        console.log("Recruiter job offer interviews", response.data)
        return response.data;
    }
    catch(error){
        throw error;
    }
}


/**
 * Retrieves the interview calendar for a recruiter.
 *
 * It retrieves a lightweight collection of interviews
 * and organizes them by day for the current month.
 * Only the data required to display the calendar is retrieved.
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




/**
 * ----------------------------
 * Services
 * ----------------------------
 */

const Queries = {
    //-- Recruiter
    getRecruiterJobOfferInterviews,
    getInterviewAgendaForRecruiter,
    getRecruiterCalendarPlaning,
    getCandidateImage,

    //-- Candidates
    getInterviewAgendaForCandidate,
    getCandidateCalendarPlaning,
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