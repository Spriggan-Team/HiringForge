import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import type { ApiResponseError } from "../../exceptions";
import { authGet } from "../../http";
import { type ApiResponse, } from "../response.types";
import type {  UserOfferQueryResponse } from "./response";


//-- Recruiters
const getUserJobOffers = async(
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

        const response = await authGet<UserOfferQueryResponse>(`/offers/user/jobs${
            params.toString() ? `?${params.toString()}` : ''
        }`);

        return response.data;
    }
    catch(error){
        throw error;
    }
}


const OffersQueries = intercept<
    {
        getUserJobOffers: typeof getUserJobOffers
    },
    ApiResponse | ApiResponseError
>(
    { getUserJobOffers },
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)

export default OffersQueries;