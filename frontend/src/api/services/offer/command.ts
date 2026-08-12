import type { CreateOfferPayload } from "../../../features/offer/offer";
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import type { ApiResponseError } from "../../exceptions";
import { authPost } from "../../http";
import type { ApiResponse } from "../response.types";


//------------------
//--- Recruiter
//-------------------

const create = async(offer: CreateOfferPayload)=>{
    try{
        const data = {
            
        };
        await authPost('/offers/user', data);
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


//--
const Queries = { create, cancelOffer , deleteOffer}

const OffersServices = intercept<
    typeof Queries,
    ApiResponse | ApiResponseError
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)


export default OffersServices;