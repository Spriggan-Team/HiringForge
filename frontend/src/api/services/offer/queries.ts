import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../handler";


//-- Recruiters
const getOffersForUser = async(jobId: string)=>{
    try{

    }
    catch(error){

    }
}

const OffersQueries = intercept(
    { getOffersForUser },
    undefined,
    handleGenericApiResponseAfter
)