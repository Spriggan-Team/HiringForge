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

const getJobsSummary = async (
    limit: number = 7,
    skip: number = 0,
    filters?: UserJobFiltersRequets
) => {
    try {
        const queryString = buildFilterQueryParams(filters, { limit, skip });
        const response = await authGet<ApiJobSummaryResponse>(`/users/job_offer?${queryString}`);
        console.log("Job summaries", response.data);
        return response.data;
    }
    catch (error) {
        throw error;
    }
};



const getJobView = async (jobOfferId: string): Promise<JobView> => {
  try {
    const response = await authGet<JobViewApiResponse>(`/users/job_offer/${jobOfferId}`);
    
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


const getPostulationMetrics = async({
    jobId,
    timeframe = 'month'
}:{
    jobId?: string;
    timeframe?: 'week' | 'month'
})=>{
    try{
        const response = await authGet<ApiResponse<number[]>>(`/users/job_offer/${jobId}/candidates/stats?timeframe=${timeframe}`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const getUserStats = async(jobId: string)=>{
    try{
        const response = await authGet<RecruitmentPipelineStatsResponse>(`/users/job_offer/${jobId}/candidates/stats`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const countJobOffers = async (currentFilters?: UserJobFiltersRequets) => {
    try {
        const queryString = buildFilterQueryParams(currentFilters);
        const response = await authGet<ApiResponse<number>>(`/users/job_offer/count?${queryString}`);
        console.log('COUNT ', response.data)
        return response.data;
    }
    catch (error) {
        throw error;
    }
};


const getJobKpis = async(
    jobId: string
)=>{
    try{
        const response = await authGet<RecruitmentMetricsResponse>(`/applications/${jobId}/kpis`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


//----JobPublicationStatus
const JobQueries = intercept(
    { 
        getJobsSummary,
        getJobView,
        getJobKpis,
        countJobOffers,
        getPostulationMetrics,
        getUserStats
    },
    undefined,
    handleGenericApiResponseAfter
);



export default JobQueries;

