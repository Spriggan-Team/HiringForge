import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";

import { authGet, authPatch } from "../../http";
import type { ApiResponseError } from "../../exceptions";

import { type ApiResponse, } from "../response.types";
import type {  CandidateEmploymentOfferResponse, CandidateEmploymentOffersStats, EmploymentOfferQueryResponse } from "./response";
import type { OfferFilter } from "../../../features/employment/offer";



//-- Recruiters
const getUserEmploymentOffer = async(
    { 
        jobId,
        skip = 0, 
        limit= 17,
        companyId 
    }:
    {
        jobId?: string, 
        skip?: number;
        limit?: number;
        companyId?: string
    } = {}
)=>{
    try{
        const params = new URLSearchParams();

        if(jobId) params.set("jobId", jobId);
        if (companyId) params.set('companyId', companyId);
        if (skip !== undefined) params.set('skip', String(skip));
        if (limit !== undefined) params.set('limit', String(limit));

        const response = await authGet<EmploymentOfferQueryResponse>(`/users/employment_offers${
            params.toString() ? `?${params.toString()}` : ''
        }`);

        return response.data;
    }
    catch(error){
        throw error;
    }
}


//----------------------
//------- Candidate
//-----------------------

/**
 * Retreive employment offer for candidate
 */
async function getCandidateEmploymentOffers({
    skip = 0,
    limit = 15,
    jobTitle,
    menu = "ALL"
}:{
    skip?: number;
    limit?: number;
    jobTitle?: string;
    menu: "PENDING" | "ACCEPTED" | "COMPLETED" | "REJECTED" | "ALL" 
})
{
    try{
        const params = new URLSearchParams();
        params.set("skip", String(skip));
        params.set("limit", String(limit));
        params.set("menu", menu);
        if(jobTitle) params.set("jobTitle", jobTitle)
        
        const url = `/candidates/employment_offers${params.toString() ? `?${params.toString()}` : ''}`
        const response = await authGet<CandidateEmploymentOfferResponse>(url);

        return response.data;
    }
    catch(error){
        throw error;
    }
} 


/**
 * Retreives candidates stats
 * @returns 
 */
async function getCandidateEmploymentOfferStats() {
    try{
        const url = '/candidates/employment_offers/stats';
        const resposne = await authGet<CandidateEmploymentOffersStats>(url);
        return resposne.data;
    }
    catch(error){
        throw error;
    }
}





//--------------------------
//-- Queries
//--------------------------


const Queries =  {
    getUserEmploymentOffer,

    //-- candidate
    getCandidateEmploymentOffers,
    getCandidateEmploymentOfferStats,
}


const EmploymentOffersQueries =intercept<
   typeof Queries,
    ApiResponse | ApiResponseError
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)

export default EmploymentOffersQueries;