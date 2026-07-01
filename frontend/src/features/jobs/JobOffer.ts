
export interface JobSummary {
  id: string;
  title: string;
  address: string;

  publicationStatus: JobPublicationStatus;
  activityStatus?: JobActivityStatus;

  cardinal: JobCardinal;
}


export interface JobCardinal {
  candidates: number;
  interviews: number;
  offers: number;
  hired: number;
}


export interface JobView{
    id: string;
    title: string;
    categories: string[];
    
    salary?: {
      min?: number;
      max?: number;
      fix?: number;
    };

    contract?: string;
    devise?: string;

    location?: {
      street?: string;
      country?: string;
      city?: string;
    }
    
    jobWorkMode?: JobWorkMode;
    publicationStatus: JobPublicationStatus;
    activityStatus: JobActivityStatus;
    
    mainImage?: string | undefined;
    content: Record<string, any>; //-- Record Quill JSON
    
    views: number;
    applications: number;

    createdAt: Date;
    updatedAt?: Date;
}

export type JobStatus = JobPublicationStatus | JobActivityStatus;
export type JobPublicationStatus = "draft" | "closed" | "published";
export type JobActivityStatus = "active" | "pending";
type JobWorkMode = "remote" | "onsite" | "hybrid";