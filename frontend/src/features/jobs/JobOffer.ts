
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
    /** JSON content (TipTap / Editor) */
    content: Record<string, any>;

    skills: {
        id: string;
        name: string;
    }[];
    categories: string[];

    salary?: Partial<Salary>;

    contract?: {
        id: string;
        label: string;
    };
    requireLanguages: JobLanguage[];
    
    location?: Partial<Location>;
    
    mainImage?: string;
    jobWorkMode?: JobWorkMode;
    
    createdAt?: Date;
    updatedAt?: Date;
    publicationDate?: Date;
}



export interface JobLanguage {
    id: number;
    code: string; //-- Code iso
    nativeLabel: string;
    proficiencyLevel: LanguageLevel;
}


export interface Salary{
  devise: string;
  min: number;
  max: number;
  fix: number;
}


export interface Location{
  id?: string;
  city: string;
  street: string;
  country: string;
}


export interface Department{
    id?: number;
    label: string;
    description?: string;
}


/** ----------------------------------------------------------------
 * Recruiter-only data
 * ---------------------------------------------------------------- */


export interface RecruiterJobView {
    activityStatus: JobActivityStatus;
    publicationStatus: JobPublicationStatus;

    visibilityStatus: visibilityStatus;

    views: number;
    applications: number;
    expertise?: string;
    cardinal: JobCardinal;

    department?: Department | null;
}


/** ----------------------------------------------------------------
 * Complete view (Recruiter / Admin)
 * ---------------------------------------------------------------- */


export type JobView = PublicJobView & RecruiterJobView;


export type JobWorkMode = "remote" | "onsite" | "hybrid";
export type JobStatus = JobPublicationStatus | JobActivityStatus;
export type JobPublicationStatus = "draft" | "closed" | "published";
export type JobActivityStatus = "active" | "pending";
export type visibilityStatus = "private" | "public"




export type LanguageLevel =  
    | 'A1' 
    | 'A2' 
    | 'B1' 
    | 'B2' 
    | 'C1' 
    | 'C2' 
    | 'native'


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

    contract: undefined,

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
    visibilityStatus: "public",

    views: 0,
    applications: 0,

    cardinal: {
        candidates: 0,
        interviews: 0,
        offers: 0,
        hired: 0,
    },

    department: null
};


export const LANGUAGES_LEVEL_VALUES: LanguageLevel[] = [
    'A1' ,'A2' ,'B1' ,'B2' ,'C1' ,'C2' , 'native'
] as const;