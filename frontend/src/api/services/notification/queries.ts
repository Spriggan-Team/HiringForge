import { authGet, authPost } from "../../http";
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";

import type { ApiResponse } from "../response.types";
import type { GetJobOfferNotificationsResponse } from "./response";
import type { NotificationTypes, NotificationValueType } from "../../../features/notfication/notification";





const getUserbNotifications = async <T= NotificationTypes>({
    jobId,
    skip = 0,
    limit = 5,
    types = []
}: {
    jobId?: string;
    skip?: number;
    limit?: number;
    types?: NotificationValueType[]
}) => {
    try {
        const params = new URLSearchParams();
        const notifTypes = types.join(",");

        params.set("skip", String(skip));
        params.set("limit", String(limit));
        params.set("types", notifTypes);

        if (jobId) {
            params.set("offerId", jobId);
        }

        const response = await authGet<GetJobOfferNotificationsResponse<T[]>>(
            `/notifications/user?${params.toString()}`
        );

        return response.data;
    }
    catch (error) {
        throw error;
    }
};



const countUnreadNotification = async ()=>{
    try{
        const response = await authGet<ApiResponse<{unreadCount: number}>>(`/notifications/account/unread-count`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const NotificationQueries = intercept(
    { 
        getUserbNotifications,
        countUnreadNotification,
    },
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result as ApiResponse)
)

export default NotificationQueries;
