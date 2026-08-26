import { intercept } from "../../../utils/utils";

import { EmploymentOfferStatus, type CreateOfferPayload } from "../../../features/employment/offer";
import { handleGenericApiResponseAfter } from "../../api-response-handler";

import type { ApiResponseError } from "../../exceptions";
import type { ApiResponse } from "../response.types";
import { authPost } from "../../http";
import type { EmploymentSavedResponse } from "./response";



//------------------
//--- Recruiter
//-------------------

const create = async(employment: CreateOfferPayload)=>{
    try{
        const body = {
            ...employment
        };
        
        const response = await authPost<EmploymentSavedResponse>('/users/employment_offers/create', body);
        const result = response.data;

        return {
            ...result,
            id: result.id,
            status: EmploymentOfferStatus.SENT,
            createdAt: result.createdAt
        }
    }
    catch(error){
        throw error;
    }
}


const cancelOffer = async (offerId: string)=>{
    try{

    }
    catch(error)
    {
        throw error;
    }
}


const deleteOffer = async (id: string)=>{
    try{

    }
    catch(error){

    }
}


//-- Services
const Services = { create, cancelOffer , deleteOffer}

const EmploymentOffersServices = intercept<
    typeof Services,
    ApiResponse | ApiResponseError
>(
    Services,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)


export default EmploymentOffersServices;