import type {  EmploymentOfferStatus } from "../../../features/employment/offer";
import type { SymfonyDateTime } from "../../../features/shared/global";
import type { ApiResponse } from "../response.types";

/** Response */
export type EmploymentOfferQueryResponse = ApiResponse<EmploymentOfferQueryData[]>;
export type EmploymentSavedResponse = ApiResponse<EmploymentSaved>;

/** Supports */
export type EmploymentOfferQueryData = {
  id: string;
  status: EmploymentOfferStatus;
  salary: number;
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
  };
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


export interface EmploymentSaved {
  id: string,
  createdAt: string
}