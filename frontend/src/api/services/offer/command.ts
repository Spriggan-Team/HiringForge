import type { CreateOfferPayload } from "../../../features/offer/offer";
import { intercept } from "../../../utils/utils";


//--- Recruiter

const create = async(offer: CreateOfferPayload)=>{
    try{

    }
    catch(error){
        throw error;
    }
}


const OffersServices = intercept(
    {create}
)

export default OffersServices;