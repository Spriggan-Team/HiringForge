import type { ApiResponse } from "../response.types";


export interface JobInterviewItem {
  // ...
}

export type RecruiterJobInterviewsResponse =
  ApiResponse<JobInterviewItem[]>;