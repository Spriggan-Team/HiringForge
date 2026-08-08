import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";
import type { RecruiterJobInterviewsResponse } from "./response";


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
        if (skip !== undefined) params.set('skip', String(skip));
        if (limit !== undefined) params.set('limit', String(limit));

        const url = `/interviews/users/job_offer/${jobId}${
            params.toString() ? `?${params.toString()}` : ''
        }`;

        const response = await authGet<RecruiterJobInterviewsResponse>(url);
        return response.data;
    }
    catch(error){
        throw error;
    }
}




const InterviewsQueries = intercept(
    { getRecruiterJobOfferInterviews },
    undefined,
    handleGenericApiResponseAfter
);


export default InterviewsQueries;