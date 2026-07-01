
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
    salary?: number;
    devise?: string;
    status: JobPublicationStatus;
    mainImage?: string | undefined;
    activityStatus: JobActivityStatus;
    content: Record<string, any>; //-- Record Quill JSON
    createdAt: Date;
    updatedAt?: Date;
}

export type JobStatus = JobPublicationStatus | JobActivityStatus;
export type JobPublicationStatus = "draft" | "closed" | "published"
export type JobActivityStatus = "active" | "pending"