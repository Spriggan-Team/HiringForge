import type { CreateOfferPayload } from "../../../features/offer/offer";
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../handler";


//--- Recruiter

const create = async(offer: CreateOfferPayload)=>{
    try{

    }
    catch(error){
        throw error;
    }
}


const OffersServices = intercept(
    { create },
    undefined,
    handleGenericApiResponseAfter
)


export default OffersServices;