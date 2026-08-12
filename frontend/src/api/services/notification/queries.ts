import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet } from "../../http";
import type { ApiResponse } from "../response.types";
import type { GetJobOfferNotificationsResponse } from "./response";




const getJobNotfication = async (jobId: string, limit: number = 5)=>{
    try{
        const response = await authGet<GetJobOfferNotificationsResponse>(`notification/user/jobs/${jobId}?limit=${limit}`);
        // console.log("Job Notification : ", response.data)
        return response.data;
    }
    catch(error){
        throw error;
    }
}



const NotificationQueries = intercept(
    { getJobNotfication },
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result as ApiResponse)
)

export default NotificationQueries;
