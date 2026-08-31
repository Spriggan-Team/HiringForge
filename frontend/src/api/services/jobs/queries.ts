import { intercept } from "../../../utils/utils";

import { type ApiResponse } from "../response.types";
import { authGet } from "../../http";
import type { 
    ApiJobSummaryResponse,
    ApplicationsLightViewApiresponse,
    CandidateListResponse,
    JobOfferCardinalitiesApiResponse,
    JobViewApiResponse,
    RecruitmentMetricsResponse,
    RecruitmentPipelineStatsResponse
} from "./response";

import { buildFilterQueryParams } from "./helpers";
import { mapJobOfferViewToJobView } from "./mapper";
import type { UserJobFiltersRequets } from "./request";
import type { JobActivityStatus, JobView } from "../../../features/jobs/JobOffer";
import { handleGenericApiResponseAfter } from "../../api-response-handler";


//----------------------------
//-- Recruiter
//----------------------------

/**
 * Get details summary about a job for an user (recruiters)
 * @param limit 
 * @param skip 
 * @param filters 
 * @returns 
 */
const getJobsSummary = async (
    limit: number = 7,
    skip: number = 0,
    filters?: UserJobFiltersRequets
) => {
    try {
        const queryString = buildFilterQueryParams(filters, { limit, skip });
        const response = await authGet<ApiJobSummaryResponse>(`/users/job_offers/summary?${queryString}`);
        console.log("Job summaries", response.data);
        return response.data;
    }
    catch (error) {
        throw error;
    }
};


/** Retrurns metrics / count data */
const getJobSummaryById = async (jobId: string)=>{
    const response = await authGet<JobOfferCardinalitiesApiResponse>(`/users/job_offers/summary/${jobId}`);
    console.log("Job summaries", response.data);
    return response.data;
}


/**
 * Retrieve light job offers by criteria
 */
const getJobOffersWithCriteria = async ({
    filters,
    skip,
    limit
}: {
    filters: {
        activityStatus?: JobActivityStatus;
    };
    skip?: number;
    limit?: number
}) => {
    try {
        const params = new URLSearchParams();

        if (skip) params.set('activityStatus', String(skip));
        if (limit) params.set('activityStatus', String(limit));
        if (filters.activityStatus) params.set('activityStatus', filters.activityStatus);

        const queryString = params.toString();
        const url = `/users/job_offers/criteria_base${
            queryString ? `?${queryString}` : ''
        }`;

        const response = await authGet<ApplicationsLightViewApiresponse>(url);

        return response.data;
    } catch (error) {
        throw error;
    }
};




/**
 * Retreive details about a job for a recruiter
 * @param jobOfferId 
 * @returns 
 */
const getJobView = async (jobOfferId: string): Promise<JobView> => {
  try {
    const response = await authGet<JobViewApiResponse>(`/users/job_offers/${jobOfferId}`);
    
    if (!response.data) {
      throw new Error("Job offer data is empty");
    }

    return mapJobOfferViewToJobView(response.data);
  }
  catch (error) {
    console.error(`Failed to fetch job view for ID ${jobOfferId}:`, error);
    throw error;
  }
};



/**
 * Count job with specify criteria
 * @param currentFilters 
 * @returns 
 */
const countJobOffers = async (currentFilters?: UserJobFiltersRequets) => {
    try {
        const queryString = buildFilterQueryParams(currentFilters);
        const response = await authGet<ApiResponse<number>>(`/users/job_offers/count?${queryString}`);
        console.log('COUNT ', response.data)
        return response.data;
    }
    catch (error) {
        throw error;
    }
};


/**
 * Retreive kpis bases related to job(s)
 * @param  {string|null} jobId if not specified the kpis are calculated based on all related job to the current user
 * @returns 
 */
const getJobKpis = async(
    jobId?: string
)=>{
    try{
        const params = new URLSearchParams();
        
        if(jobId) params.set('jobId', jobId);

        const response = await authGet<RecruitmentMetricsResponse>(`/users/job_offers/kpis${
            params.toString() ? `?${params.toString()}` : ''
        }`);
        
        return response.data;
    }
    catch(error){
        throw error;
    }
}


/**
 * Retreive stats about a specific user of jobId specified.
 * Otherwise retreive jobs stats related to all jobs created by the current user (recruiter)
 * @param jobId 
 * @returns 
 */
const getJobOffersOverview = async(jobId?: string)=>{
    try{
        const params = new URLSearchParams();

        if (jobId) {
            params.set('jobId', jobId);
        }

        const response = await authGet<RecruitmentPipelineStatsResponse>(
            `/users/job_offers/stats${
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
 * Retrieves the light model of candidates associated with
 * the specified job offer, with optional search and pagination.
 *
 * @param params - Query parameters used to retrieve candidates.
 * @param params.jobId - The ID of the job offer.
 * @param params.search - Optional search term used to filter candidates.
 * @param params.limit - Maximum number of candidates to retrieve.
 * @returns A list of candidates associated with the job offer.
 */
const getJobCandidates = async(
    {
        jobId,
        search,
        limit,
    }:{
        jobId: string,
        search: string,
        limit: number,
    }
)=>{
    try{
        const response = await authGet<CandidateListResponse>('');
        return response.data;
    }
    catch(error){
        throw error;
    }
}




//-------------------------
// Services
//-------------------------

const JobQueries = intercept(
    { 
        getJobView,
        getJobsSummary,
        getJobOffersWithCriteria,
        getJobSummaryById,

            //-- Stats
        countJobOffers,
        getJobOffersOverview,
    
        getJobCandidates,
        getJobKpis,
    },
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result as ApiResponse)
);



export default JobQueries;

