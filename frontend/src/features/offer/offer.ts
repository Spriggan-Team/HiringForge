
export const OfferStatus = {
  SENT: 'SENT',
  ACCEPTED: 'ACCEPTED',
  DECLINED: 'DECLINED',
  EXPIRED: 'EXPIRED',
  DRAFT: 'DRAFT',
  CANCELLED: 'CANCELLED'
} as const;


export type OfferStatus = typeof OfferStatus[keyof typeof OfferStatus];


export type OfferSummary = {
    id: string;
    title: string | null;
    message: string | null;
    salary: number | null;
    status: OfferStatus;
    sentAt: string;
    expiredAt: string;
    application: {
        id: string;
        jobTitle: string;
    };
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


export interface CreateOfferPayload {
    applicationId: string;
    candidateId: string;
    title: string;
    message: string;
    salary: number;
    expiredAt: string;
}

export const canDeleteOffer = (status: OfferStatus): boolean => {
    return status === 'DRAFT'; //Only draft can be deleted
};

export const canCancelOffer = (status: OfferStatus): boolean => {
    return status === 'SENT'; // Offer sent but not accepted yet!!
};