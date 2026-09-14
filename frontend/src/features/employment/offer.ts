import type { SymfonyDateTime } from "../shared/global";

export const EmploymentOfferStatus = {
  SENT: 'SENT',
  ACCEPTED: 'ACCEPTED',
  DECLINED: 'DECLINED',
  EXPIRED: 'EXPIRED',
  DRAFT: 'DRAFT',
  CANCELLED: 'CANCELLED'
} as const;


export type EmploymentOfferStatus = typeof EmploymentOfferStatus[keyof typeof EmploymentOfferStatus];


export type OfferSummary = {
    id: string;
    title: string | null;
    message: string | null;
    salary: number | null;
    status: EmploymentOfferStatus;
    sentAt: string;
    expiredAt: string;
    application: {
        id: string;
        jobTitle: string;
    };
}

/** Data object (For candidate & recruiter) */
export type RecruiterEmploymentOffer = EmploymentOfferData & RecruiterEmploymentOfferFields;
export type CandidateEmploymentOffer = EmploymentOfferData & CandidateEmploymentOfferFields;

/** Details about type compisiton (components) */
export type EmploymentOfferData = {
  id: string;
  status: EmploymentOfferStatus;
  salary: number;
  message?: string | null;
  application: {
    id: string;
  };
  jobOffer:{
    id: string;
    title: string;
  },

  rejectionReason?: string;

  scheduledEndDate: SymfonyDateTime;
  expiredAt: SymfonyDateTime;
  createdAt: SymfonyDateTime;
};


export interface EmploymentOfferStats{
    awaiting: number;
    accepted: number;
    completed: number;
    rejected: number;
}


//------ Recruiter

export type RecruiterOffer = OfferSummary & {
    candidate: {
        id: string;
        firstName: string;
        lastName: string;
        email: string;
    };
}


export interface RecruiterEmploymentOfferFields{
   candidate: {
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    image: {
      id: string;
      mime?: string;
      name?: string
    }
  }
}


export interface CandidateEmploymentOfferFields{
    company:{
        name: string;
        logoUrl?: string;
    }
}


export interface CreateOfferPayload {
    applicationId: string;
    candidateId: string;
    message: string;
    salary: number;
    expiredAt: string;
    jobTitle: string;
    scheduledEndDate: string;
}



/**
 * Funtion Helpers
 */

export const canDeleteOffer = (status: EmploymentOfferStatus): boolean => {
    return status === 'DRAFT'; //Only draft can be deleted
};

export const canCancelOffer = (status: EmploymentOfferStatus): boolean => {
    return status === 'SENT'; // Offer sent but not accepted yet!!
};





//----------------------------------------
//-- for ui - recruteur view (react components)
//----------------------------------------

export interface FlatOffer {
    id: string;
    candidate: string;
    email: string;
    
    jobTitle: string;
    salary?: number; // Ex: 45000 (en €/an)

    status: EmploymentOfferStatus;
    avatarUrl?: string | null;
    message?: string | null;

    createdAt: string;
    expiresAt: string;
    scheduledEndDate: string;
    rejectionReason?: string;
}


export type OfferFilter =
    | "ALL"
    | "AWAITING"
    | "ACCEPTED"
    | "COMPLETED"
    | "REJECTED";