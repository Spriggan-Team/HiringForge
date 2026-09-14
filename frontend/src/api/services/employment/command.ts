import { intercept } from "../../../utils/utils";

import { EmploymentOfferStatus, type CreateOfferPayload } from "../../../features/employment/offer";
import { handleGenericApiResponseAfter } from "../../api-response-handler";

import type { ApiResponseError } from "../../exceptions";
import type { ApiResponse } from "../response.types";

import { authDel, authPatch, authPost } from "../../http";
import type { EmploymentSavedResponse } from "./response";



//------------------
//--- Recruiter
//-------------------



const create = async(employment: CreateOfferPayload)=>{
    try{
        const body = {
            ...employment
        };
        
        const response = await authPost<EmploymentSavedResponse>(
            '/users/employment_offers/create', body
        );
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


const cancelOffer = async (employmentOfferId: string)=>{
    try{
        await authDel(`/users/employment_offers/${employmentOfferId}/cancel`);
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


//-------------------------
//--- Candidate
//-------------------------

/**
 * A way for candidate to accept or reject an employment offer
 * @param params 
 */
async function handleAcceptEmploymentOffer({
    employmentId
}:{
    employmentId: string
}) {
    try{
        const url = `/candidates/employment_offers/accept`;
        await authPatch(url, { employmentId });
    }
    catch(error){
        throw error;
    }
}

/**
 * handle refgusing
 * @param param0 
 */
async function handleRefuseEmploymentOffer({
    employmentId,
    rejectionReason
}:{
    employmentId: string,
    rejectionReason: string
}) {
    try{
        const url = `/candidates/employment_offers/refuse`;
        await authPatch(url, { employmentId, rejectionReason });
    }
    catch(error){
        throw error;
    }
}


//-- Services
const Services = { 
    create, 
    cancelOffer ,
    deleteOffer,

    handleAcceptEmploymentOffer,
    handleRefuseEmploymentOffer
}


const EmploymentOffersServices = intercept<
    typeof Services,
    ApiResponse | ApiResponseError
>(
    Services,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)


export default EmploymentOffersServices;