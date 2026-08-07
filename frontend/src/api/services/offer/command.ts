import type { CreateOfferPayload } from "../../../features/offer/offer";
import { intercept } from "../../../utils/utils";
import { authPost, handleGenericApiResponseAfter } from "../../handler";


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


const OffersServices = intercept(
    { create, cancelOffer , deleteOffer},
    undefined,
    handleGenericApiResponseAfter
)


export default OffersServices;