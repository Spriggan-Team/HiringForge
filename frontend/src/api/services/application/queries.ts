import { intercept } from "../../../utils/utils"
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet } from "../../http"
import type { ApiResponse, ErrorApiResponse } from "../response.types";
import type { JobApplicationApiResponse } from "./response";


//-- Recruiter
const getApplications = async (
  {
    jobId,
    companyId,
    skip,
    limit,
    search,
    signal
  }: {
    signal?: AbortSignal;
    search?:string;
    jobId?: string;
    companyId?: string;
    skip?: number;
    limit?: number;
  } = {}
) => {
  try {
    const params = new URLSearchParams();

    if(jobId) params.set('jobId', jobId)
    if (companyId) params.set('companyId', companyId);
    if(search) params.set("search", search)
    if (skip !== undefined) params.set('skip', String(skip));
    if (limit !== undefined) params.set('limit', String(limit));

    const url = `/users/applications/job_offers${
      params.toString() ? `?${params.toString()}` : ''
    }`;

    const response = await authGet<JobApplicationApiResponse>(url);
    return response.data;
  }
  catch (error) {
    throw error;
  }
};



/**
 * Retreive postulation metrics about job(s)
 * if 
 * @param param0 
 * @returns 
 */
const getJobPostulationMetrics = async({
    jobId,
    timeframe = 'month'
}:{
    jobId?: string;
    timeframe?: 'week' | 'month'
})=>{
    try{
      const params = new URLSearchParams();

      if (jobId) params.set('jobId', jobId);
      if (timeframe) params.set('timeframe', timeframe);

      const response = await authGet<ApiResponse<number[]>>(
          `/users/applications/stats${
              params.toString() ? `?${params.toString()}` : ''
          }`
      );

      return response.data;
    }
    catch(error){
        throw error;
    }
}



const countRejected = async (jobId: string)=>{
  try{
    const response = await authGet<ApiResponse<number>>(`/users/applications/${jobId}/rejected`);
    return response.data;
  }
  catch(error){
    throw error;
  }
}

//-- User


const Queries = { 
  getApplications,
  countRejected,
  getJobPostulationMetrics
}

const ApplicationQueries = intercept<
  typeof Queries,
  ApiResponse | ErrorApiResponse
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)

export default ApplicationQueries;