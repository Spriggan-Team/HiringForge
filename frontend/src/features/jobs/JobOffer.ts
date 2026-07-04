
/** ----------------------------------------------------------------
 * Job Summary (Recruiter)
 * ---------------------------------------------------------------- */

export interface JobSummary {
  id: string;
  title: string;
  address: string;

  publicationStatus: JobPublicationStatus;
  activityStatus?: JobActivityStatus;

  cardinal: JobCardinal;
}

/** ----------------------------------------------------------------
 * Job offer Cardinal info (Recruiter)
 * ---------------------------------------------------------------- */

export interface JobCardinal {
  candidates: number;
  interviews: number;
  offers: number;
  hired: number;
}

/** ----------------------------------------------------------------
 * Shared (Candidate + Recruiter)
 * ---------------------------------------------------------------- */

export interface PublicJobView {
    id: string;

    title: string;

    skills: string[];
    categories: string[];

    salary?: Partial<Salary>;

    contract?: string;
    requireLanguages: JobLanguage[];
    
    location?: Partial<Location>;
    
    mainImage?: string;
    jobWorkMode?: JobWorkMode;

    
    /** JSON content (TipTap / Editor) */
    content: Record<string, any>;
    
    createdAt?: Date;
    updatedAt?: Date;
    publicationDate?: Date;
}



interface JobLanguage {
    languageCode: string; //-- Code iso
    proficiencyLevel: LanguageLevel;
}


export interface Salary{
  devise: string;
  min: number;
  max: number;
  fix: number;
}


export interface Location{
  city: string;
  street: string;
  country: string;
}



/** ----------------------------------------------------------------
 * Recruiter-only data
 * ---------------------------------------------------------------- */


export interface RecruiterJobView {
    activityStatus: JobActivityStatus;
    publicationStatus: JobPublicationStatus;

    views: number;
    applications: number;
}


/** ----------------------------------------------------------------
 * Complete view (Recruiter / Admin)
 * ---------------------------------------------------------------- */


export type JobView = PublicJobView & RecruiterJobView;


type JobWorkMode = "remote" | "onsite" | "hybrid";
export type JobStatus = JobPublicationStatus | JobActivityStatus;
export type JobPublicationStatus = "draft" | "closed" | "published";
export type JobActivityStatus = "active" | "pending";

export type LanguageLevel =
    | "beginner"
    | "intermediate"
    | "advanced"
    | "fluent"
    | "native";


/**
 * ---------------------
 * Empty job view
 * ---------------------
 */


export const INITIAL_JOB_VIEW: PublicJobView & RecruiterJobView = {
    /** PublicJobView */
    id: "",

    title: "",

    categories: [],
    skills: [],

    salary: {
        min: 0,
        max: 0,
        fix: 0,
        devise: "",
    },
    requireLanguages: [],

    contract: "",

    location: {
        city: "",
        street: "",
        country: "",
    },

    jobWorkMode: "onsite",

    mainImage: "",

    content: {},

    createdAt: undefined,
    updatedAt: undefined,
    publicationDate: undefined,

    /** RecruiterJobView */
    activityStatus: "pending",
    publicationStatus: "draft",

    views: 0,
    applications: 0,
};