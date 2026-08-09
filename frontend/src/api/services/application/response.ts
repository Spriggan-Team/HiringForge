import type { ApplicationStatusValue } from "../../../features/application/application";
import type { ApiResponse } from "../response.types";


export type JobApplicationApiResponse = ApiResponse<JobApplicationItem[]>;

export interface JobApplicationItem {
  id: string;
  status: ApplicationStatusValue;
  candidate: {
    email: string;
    firstName: string;
    lastName: string;
    imageUrl?: string;
  };
  matchScore: number;
  appliedAt: Date; // Date

  jobOffer?:{
    id: string;
    title: string
  }
}