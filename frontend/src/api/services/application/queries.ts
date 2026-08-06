import { intercept } from "../../../utils/utils"
import { authGet, handleGenericApiResponseAfter } from "../../handler"
import type { ApiResponse } from "../response.types";
import type { JobApplicationApiResponse } from "./response";


//-- Recruiter
const getApplicationsForJob = async (
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
) => {
  try {
    const params = new URLSearchParams();

    if (companyId) params.set('companyId', companyId);
    if (skip !== undefined) params.set('skip', String(skip));
    if (limit !== undefined) params.set('limit', String(limit));

    const url = `/applications/job_offer/${jobId}${
      params.toString() ? `?${params.toString()}` : ''
    }`;

    const response = await authGet<JobApplicationApiResponse>(url);
    return response.data;
  }
  catch (error) {
    throw error;
  }
};


const countRejected = async (jobId: string)=>{
  try{
    const response = await authGet<ApiResponse<number>>(`/applications/${jobId}/rejected`);
    return response.data;
  }
  catch(error){
    throw error;
  }
}

//-- User



const ApplicationQueries = intercept(
    { getApplicationsForJob, countRejected },
    undefined,
    handleGenericApiResponseAfter
)

export default ApplicationQueries;