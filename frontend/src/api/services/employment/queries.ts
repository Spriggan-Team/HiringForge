import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";

import { authGet } from "../../http";
import type { ApiResponseError } from "../../exceptions";

import { type ApiResponse, } from "../response.types";
import type {  EmploymentOfferQueryResponse } from "./response";


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



const Queries =  {
    getUserEmploymentOffer
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