import type { ApiResponse } from "../response.types";
import type { CandidateLightModel } from "../../../features/candidates/candidates";
import type { 
  JobActivityStatus,
  JobPublicationStatus,
  JobSummary,
  JobView,
  JobWorkMode,
  LanguageLevel,
  VisibilityStatus
} from "../../../features/jobs/JobOffer";


//---------------------
//----- Candidates
//------------------------


export type CandidateListResponse = ApiResponse<CandidateLightModel[]>;


//------------------
//-- Job
//------------------

export type JobSummaryItem  = JobSummary;

export type ApiJobSummaryResponse  = ApiResponse<JobSummaryItem[]>;


export type JobOfferViewDataResponse = {
  id: string;
  title: string;
  content: Record<string, any>;
  jobWorkMode?: JobWorkMode;
  mainImage?: string | null;
  viewsCount?: number;
  createdAt?: string;
  updatedAt?: string;
  activityStatus: JobActivityStatus, // Valeur par défaut / calculée si absente de la projection
  publicationStatus: JobPublicationStatus,
  visibilityStatus: VisibilityStatus
  
  salary?: {
    devise?: string;
    min?: number;
    max?: number;
  };
  location?: {
    id?: string;
    street?: string;
    city?: string;
    country?: string;
  };
  department?: {
    id?: number | string;
    label?: string;
  };
  contract?: {
    id?: string;
    label?: string;
  };
  skills?: {
    id: string;
    name: string;
    isRequired?: boolean;
  }[];
  languages?: {
    label: string;
    level: LanguageLevel;
  }[];
};

export type JobViewApiResponse = ApiResponse<JobOfferViewDataResponse>;

//------------------------
//----- Statistics
//------------------------


export type RecruitmentPipelineStats = {
    preselect: number;
    interviews: number;
    rejected: number;
    offer: number;
};

export type RecruitmentPipelineStatsResponse =
    ApiResponse<RecruitmentPipelineStats>;



export type RecruitmentMetricsResponse = ApiResponse<RecruitmentMetrics>;

interface RecruitmentMetrics {
  totalApplications: number;
  applicationIncreaseThisWeek: number;
  rejectionRate: number;
  rejectedCandidatesCount: number;
  offersDeclined: number;
  offersAccepted: number;
  avgTimeToHireDays: number;
  avgTimeToHireDiffDays: number;
  offersGenerated: number;
}