import type { Notification } from "../../../features/notfication/notification";
import type { ApiResponse } from "../response.types";


export type JobNotificationApiResponse = ApiResponse<JobNotification> ;

export type JobNotification = Notification