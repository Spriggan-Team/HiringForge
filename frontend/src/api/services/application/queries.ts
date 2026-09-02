import { authGet } from "../../http"
import { intercept } from "../../../utils/utils"
import { handleGenericApiResponseAfter } from "../../api-response-handler";


import type { ApiResponse, ErrorApiResponse } from "../response.types";
import type { 
  CandidateApplicationListResponse,
  CandidatePipelineItemResponse,
  CandidatePipelineResponse,
  JobApplicationApiResponse
} from "./response";
import type {  CandidatePipelineTypeValue } from "../shared/reponses.types";
import type { ApplicationStatusValue } from "../../../features/application/application";



//--------------------
//--- Recruiter
//--------------------

/**
 * Get candidate pipeline
 */
const getCandidatesPipeline = async ({
  skip = 0, limit = 5
}: {
  skip?: number;
  limit?: number;
})=>{
  const params = new URLSearchParams();

  if (skip !== undefined) params.set('skip', String(skip));
  if (limit !== undefined) params.set('limit', String(limit));

  const url = `/users/applications/pipeline${
    params.toString() ? `?${params.toString()}` : ''
  }`;

  const response = await authGet<CandidatePipelineResponse>(url);
  return response.data;
}


/**
 * Load specific pipeline stage
 * @param param0 
 * @returns 
 */
const getCandidatePipelineStage = async ({
  type,
  skip = 0,
  limit = 5
}:{
    type: CandidatePipelineTypeValue;
    skip?: number;
    limit?: number;
})=>{
  const params = new URLSearchParams();

  params.set("type", String(type));
  if (skip !== undefined) params.set('skip', String(skip));
  if (limit !== undefined) params.set('limit', String(limit));

  const url = `/users/applications/pipeline/stage${
    params.toString() ? `?${params.toString()}` : ''
  }`;

  const response = await authGet<CandidatePipelineItemResponse>(url);
  
  return response.data;
}


/**
 * Get applications
 * @param param0 
 * @returns 
 */
const getApplications = async (
  {
    jobId,
    companyId,
    skip,
    limit,
    search,
    signal,
    statuses,
  }: {
    signal?: AbortSignal;
    search?:string;
    jobId?: string;
    companyId?: string;
    skip?: number;
    limit?: number;
    statuses?: ApplicationStatusValue[] | null
  } = {}
) => {
  try {
    const params = new URLSearchParams();

    if(jobId) params.set('jobId', jobId)
    if (companyId) params.set('companyId', companyId);
    if(search) params.set("search", search)
    if (skip !== undefined) params.set('skip', String(skip));
    if (limit !== undefined) params.set('limit', String(limit));
    if(statuses) params.set("statuses", JSON.stringify(statuses));

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
 * Search candidates withing the system using application as root
 * @param query 
 */
const searchCandidateByApplication = async (query: string) => {
  try {
    // Transmettre la query à l'API
    const response = await authGet<CandidateApplicationListResponse>(
      `/users/applications/candidates/search?query=${encodeURIComponent(query)}`
    ); 
    return response.data ?? [];
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


/**
 * Get candidate image
 * @param param0 
 * @returns 
 */
async function getCandidateProfilImage({
    candidateId,
    applicationId
  }: 
  {
    candidateId: string,
    applicationId: string
  })
{
  try{
    const blob = await authGet<Blob>(`/users/applications/${applicationId}/candidates/${candidateId}/profile-image`);
    return blob;
  }
  catch(error){
    throw error;
  }
}



/**
 * Get content about candidat resume
 * @param param0 
 * @returns 
 */
const  getCandidateResume = async ({
  candidateId,
  applicationId
}:{
  candidateId: string,
  applicationId: string
})=>{
  try{
    const blob = authGet<Blob>(`/users/applications/${applicationId}/candidates/${candidateId}/resume`);
    return blob;
  }
  catch(error){
    throw error;
  }
}



/**
 * Count number of rejected applications
 * @param jobId 
 * @returns 
 */
const countRejected = async (jobId: string)=>{
  try{
    const response = await authGet<ApiResponse<number>>(`/users/applications/${jobId}/rejected`);
    return response.data;
  }
  catch(error){
    throw error;
  }
}


//-------------------------------
//     Queries
//-------------------------------

const Queries = { 
  getCandidatesPipeline,
  getCandidatePipelineStage,

  getApplications,
  countRejected,
  
  getJobPostulationMetrics,
  searchCandidateByApplication,

  getCandidateProfilImage,
  getCandidateResume
}


const ApplicationQueries = intercept<
  typeof Queries,
  ApiResponse | ErrorApiResponse
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
);


export default ApplicationQueries;