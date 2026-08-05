import { intercept } from "../../../utils/utils";

import { type ApiResponse } from "../response.types";
import { authGet, handleGenericApiResponseAfter } from "../../handler";
import type { ApiJobSummaryResponse, JobViewApiResponse, RecruitmentPipelineStatsResponse } from "./response";
import type { UserJobFiltersRequets } from "./request";
import { buildFilterQueryParams } from "./helpers";
import type { JobView } from "../../../features/jobs/JobOffer";
import { mapJobOfferViewToJobView } from "./mapper";


//-- Recruiter


export const getJobsSummary = async (
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



export const getJobView = async (jobOfferId: string): Promise<JobView> => {
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


export const getPostulationMetrics = async({
    jobId,
    frequency = 'month'
}:{
    jobId?: string;
    frequency?: 'week' | 'month'
})=>{
    try{
        const response = await authGet('/users/job_offer');
        return response.data;
    }
    catch(error){
        throw error;
    }
}


export const getUserStats = async(jobId: string)=>{
    try{
        const response = await authGet<RecruitmentPipelineStatsResponse>(`/users/job_offer/${jobId}/candidates/stats`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


export const countJobOffers = async (currentFilters?: UserJobFiltersRequets) => {
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




//JobPublicationStatus
const JobQueries = intercept(
    {getJobsSummary, getJobView, countJobOffers, getPostulationMetrics, getUserStats},
    undefined,
    handleGenericApiResponseAfter
);



export default JobQueries;

