import { intercept } from "../../../utils/utils"
;
import { authPost } from "../../http";
import { handleGenericApiResponseAfter } from "../../api-response-handler";

import type { ApiResponse, ErrorApiResponse } from "../response.types";



const markNotificationAsRead = async (
    notificationIds: string[]
)=>{
    const url = "/notifications/account/read";
    await authPost(`${url}`, { notificationIds });
}



const Services = {
    markNotificationAsRead
}


const NotificationServices = intercept<
    typeof Services,
    ApiResponse | ErrorApiResponse
>(
    Services,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)

export default NotificationServices;