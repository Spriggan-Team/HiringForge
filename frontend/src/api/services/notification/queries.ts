import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";
import type { GetJobOfferNotificationsResponse } from "./response";




const getJobNotfication = async (jobId: string, limit: number = 5)=>{
    try{
        const response = await authGet<GetJobOfferNotificationsResponse>(`notification/user/jobs/${jobId}?limit=${limit}`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}



const NotificationQueries = intercept(
    { getJobNotfication },
    undefined,
    handleGenericApiResponseAfter
)

export default NotificationQueries;
