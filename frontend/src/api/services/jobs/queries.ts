import { intercept } from "../../../utils/utils";

import { type ApiResponse } from "../response.types";
import { authGet, handleGenericApiResponseAfter } from "../../handler";
import type { ApiJobSummaryResponse, JobViewApiResponse, RecruitmentMetricsResponse, RecruitmentPipelineStatsResponse } from "./response";

import { buildFilterQueryParams } from "./helpers";
import { mapJobOfferViewToJobView } from "./mapper";
import type { UserJobFiltersRequets } from "./request";
import type { JobView } from "../../../features/jobs/JobOffer";


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
        const response = await authGet<ApiJobSummaryResponse>(`/users/job_offers?${queryString}`);
        console.log("Job summaries", response.data);
        return response.data;
    }
    catch (error) {
        throw error;
    }
};


//--------------------------------
//---- Stats (Recruiters)
//--------------------------------

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

        const response = await authGet<RecruitmentMetricsResponse>(`/users/applications/kpis${
            params.toString() ? params.toString() : ''
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
        if(jobId) params.set("jobId", jobId);

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



//------------------------------
//--- Candidates
//-------------------------------


//-------------------------
// Services
//-------------------------

const JobQueries = intercept(
    { 
        getJobsSummary,
        getJobView,
            //-- Stats
        getJobKpis,
        countJobOffers,
        getJobOffersOverview,
    },
    undefined,
    handleGenericApiResponseAfter
);



export default JobQueries;

