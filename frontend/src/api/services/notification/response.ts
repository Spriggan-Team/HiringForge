import type {  NotificationTypes } from "../../../features/notfication/notification";
import type { ApiResponse } from "../response.types";


export type GetJobOfferNotificationsResponse<T = NotificationTypes> = ApiResponse<T>;
