import type { ApplicationStatusValue } from "../../../features/application/application";
import type { CandidatePipelineItem,  } from "../shared/reponses.types";

import type { ApiResponse } from "../response.types";
import type { InterviewTypeValue } from "../../../features/interviews/interviews";


/**Response */
export type JobApplicationApiResponse = ApiResponse<JobApplicationItem[]>;

export type CandidateApplicationListResponse = ApiResponse<CandidateApplication[]>; //Search candidate using application as root

export type CandidatePipelineResponse = ApiResponse<CandidatePipelineItem[]>;

export type CandidatePipelineItemResponse = ApiResponse<CandidatePipelineItem>;

/** Data body */

/** Light job offer model */
export interface JobApplicationItem {
  id: string;
  status: ApplicationStatusValue;
  candidate: {
    id: string;
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
  },

  interviews?: {
    id: string;
    startdate: string; // ISO
    type: InterviewTypeValue;
    minutes: number;
  }[]
}

/** Candidate Search using applicaton */

export interface CandidateApplication {
  applicationId: string;
  candidateId: string;
  firstName: string;
  lastName: string;
  email: string;
  jobOfferId: string;
  jobTitle: string;
  jobImageUrl?: string;
}




