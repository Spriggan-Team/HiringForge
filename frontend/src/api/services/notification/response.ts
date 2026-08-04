import type { CandidateRejectedNotification, InterviewScheduledNotification, JobAppliedNotification, Notification } from "../../../features/notfication/notification";
import type { ApiResponse } from "../response.types";


export type GetJobOfferNotificationsResponse = ApiResponse<JobOfferNotification[]>;

/** Type for /user/jobs/{offerId} */
export type JobOfferNotification = 
  | JobAppliedNotification
  | InterviewScheduledNotification
  | CandidateRejectedNotification;
