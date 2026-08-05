import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";
import type { RecruiterJobInterviewsResponse } from "./response";


const getRecruiterJobOfferInterviws = async (
  jobId: string,
  {
    companyId,
    skip,
    limit,
  }: {
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

        const url = `/interviews/job_offer/${jobId}${
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
    { getRecruiterJobOfferInterviws },
    undefined,
    handleGenericApiResponseAfter
);


export default InterviewsQueries;