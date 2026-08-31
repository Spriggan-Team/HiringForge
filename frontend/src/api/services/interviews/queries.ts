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



//Recruiter
const getRecruiterJobOfferInterviews = async (
   {
    jobId,
    companyId,
    skip,
    limit,
  }: {
    jobId?: string;
    companyId?: string;
    skip?: number;
    limit?: number;
  } = {}
)=>{
    try{
        const params = new URLSearchParams();

        if (companyId) params.set('companyId', companyId);
        if (skip) params.set('skip', String(skip));
        if (limit) params.set('limit', String(limit));
        if (jobId) params.set('jobId', jobId)

        const url = `/interviews/users/job_offers${
            params.toString() ? `?${params.toString()}` : ''
        }`;

        const response = await authGet<RecruiterJobInterviewsResponse>(url);
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
    getInterviewAgendaForRecruiter
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